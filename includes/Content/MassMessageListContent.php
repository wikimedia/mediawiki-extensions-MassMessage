<?php

namespace MediaWiki\MassMessage\Content;

use MediaWiki\Content\JsonContent;
use MediaWiki\MainConfigNames;
use MediaWiki\MassMessage\Lookup\DatabaseLookup;
use MediaWiki\MassMessage\UrlHelper;
use MediaWiki\MediaWikiServices;
use MediaWiki\Title\Title;

class MassMessageListContent extends JsonContent {

	/**
	 * Description wikitext.
	 *
	 * @var string|null
	 */
	protected $description;

	/**
	 * Array of target pages.
	 *
	 * @var array[]|null
	 */
	protected $targets;

	/**
	 * Whether $description and $targets have been populated.
	 *
	 * @var bool
	 */
	protected $decoded = false;

	/**
	 * @param string $text
	 */
	public function __construct( $text ) {
		parent::__construct( $text, 'MassMessageListContent' );
	}

	/**
	 * Decode and validate the contents.
	 *
	 * @return bool Whether the contents are valid
	 */
	public function isValid() {
		// Populate $this->description and $this->targets.
		$this->decode();

		if ( !is_string( $this->description ) || !is_array( $this->targets ) ) {
			return false;
		}
		foreach ( $this->getTargets() as $target ) {
			if ( !is_array( $target )
				|| !array_key_exists( 'title', $target )
				|| Title::newFromText( $target['title'] ) === null
			) {
				return false;
			}
		}
		return true;
	}

	/**
	 * Whether the content object contains invalid targets.
	 *
	 * @return bool
	 */
	public function hasInvalidTargets() {
		return count( $this->getTargets() ) !== count( $this->getValidTargets() );
	}

	/**
	 * @return string|null
	 */
	public function getDescription() {
		$this->decode();
		return $this->description;
	}

	/**
	 * @return array[]
	 */
	public function getTargets() {
		$this->decode();
		if ( is_array( $this->targets ) ) {
			return $this->targets;
		}
		return [];
	}

	/**
	 * Return only the targets that would be valid for delivery.
	 *
	 * @return array
	 */
	public function getValidTargets() {
		$targets = $this->getTargets();
		$validTargets = [];
		foreach ( $targets as $target ) {
			if ( !array_key_exists( 'site', $target )
				|| ( MediaWikiServices::getInstance()->getMainConfig()->get( 'AllowGlobalMessaging' )
				&& DatabaseLookup::getDBName( $target['site'] ) !== null )
			) {
				$validTargets[] = $target;
			}
		}
		return $validTargets;
	}

	/**
	 * Return targets as an array of title or title@site strings.
	 *
	 * @return array
	 */
	public function getTargetStrings() {
		$targets = $this->getTargets();
		$targetStrings = [];
		foreach ( $targets as $target ) {
			if ( array_key_exists( 'site', $target ) ) {
				$targetStrings[] = $target['title'] . '@' . $target['site'];
			} elseif ( strpos( $target['title'], '@' ) !== false ) {
				// List the site if it'd otherwise be ambiguous
				$targetStrings[] = $target['title'] . '@'
					. UrlHelper::getBaseUrl(
						MediaWikiServices::getInstance()->getMainConfig()
							->get( MainConfigNames::CanonicalServer )
					);
			} else {
				$targetStrings[] = $target['title'];
			}
		}
		return $targetStrings;
	}

	/**
	 * Decode the JSON contents and populate $description and $targets.
	 */
	protected function decode() {
		if ( $this->decoded ) {
			return;
		}
		$jsonParse = $this->getData();
		$data = $jsonParse->isGood() ? $jsonParse->getValue() : null;
		if ( $data ) {
			$this->description = $data->description ?? null;
			if ( isset( $data->targets ) && is_array( $data->targets ) ) {
				$this->targets = [];
				foreach ( $data->targets as $target ) {
					if ( !is_object( $target ) ) {
						// There is a malformed target.
						$this->targets = null;
						break;
					}
					// Convert to associative array.
					$this->targets[] = wfObjectToArray( $target );
				}
			} else {
				$this->targets = null;
			}
		}
		$this->decoded = true;
	}
}

<?php

namespace MediaWiki\MassMessage;

use ApiMain;
use ApiUsageException;
use DerivativeContext;
use DerivativeRequest;
use FormatJson;
use IContextSource;
use JsonContentHandler;
use Status;
use Title;

class MassMessageListContentHandler extends JsonContentHandler {

	/**
	 * @param string $modelId
	 */
	public function __construct( $modelId = 'MassMessageListContent' ) {
		parent::__construct( $modelId );
	}

	/**
	 * @return MassMessageListContent
	 */
	public function makeEmptyContent() {
		return new MassMessageListContent( '{"description":"","targets":[]}' );
	}

	/**
	 * @return string
	 */
	protected function getContentClass() {
		return MassMessageListContent::class;
	}

	/**
	 * @return string
	 */
	protected function getDiffEngineClass() {
		return MassMessageListDiffEngine::class;
	}

	/**
	 * @return bool
	 */
	public function isParserCacheSupported() {
		return true;
	}

	/**
	 * Edit a delivery list via the edit API
	 * @param Title $title
	 * @param string $description
	 * @param array $targets
	 * @param string $summary Message key for edit summary
	 * @param IContextSource $context The calling context
	 * @return Status
	 */
	public static function edit( Title $title, $description, $targets, $summary,
		IContextSource $context
	) {
		$jsonText = FormatJson::encode(
			[ 'description' => $description, 'targets' => $targets ]
		);
		if ( $jsonText === null ) {
			return Status::newFatal( 'massmessage-ch-tojsonerror' );
		}

		// Ensure that a valid context is provided to the API in unit tests
		$der = new DerivativeContext( $context );
		$request = new DerivativeRequest(
			$context->getRequest(),
			[
				'action' => 'edit',
				'title' => $title->getFullText(),
				'contentmodel' => 'MassMessageListContent',
				'text' => $jsonText,
				'summary' => $summary,
				'token' => $context->getUser()->getEditToken(),
			],
			true // Treat data as POSTed
		);
		$der->setRequest( $request );

		try {
			$api = new ApiMain( $der, true );
			$api->execute();
		} catch ( ApiUsageException $e ) {
			return Status::wrap( $e->getStatusValue() );
		}
		return Status::newGood();
	}

	/**
	 * Deduplicate and sort a target array
	 * @param array $targets
	 * @return array
	 */
	public static function normalizeTargetArray( $targets ) {
		$targets = array_unique( $targets, SORT_REGULAR );
		usort( $targets, 'self::compareTargets' );
		return $targets;
	}

	/**
	 * Compare two targets for ordering
	 * @param array $a
	 * @param array $b
	 * @return int
	 */
	public static function compareTargets( $a, $b ) {
		if ( !array_key_exists( 'site', $a ) && array_key_exists( 'site', $b ) ) {
			return -1;
		} elseif ( array_key_exists( 'site', $a ) && !array_key_exists( 'site', $b ) ) {
			return 1;
		} elseif ( array_key_exists( 'site', $a ) && array_key_exists( 'site', $b )
			&& $a['site'] !== $b['site']
		) {
			return strcmp( $a['site'], $b['site'] );
		} else {
			return strcmp( $a['title'], $b['title'] );
		}
	}

	/**
	 * Helper function to extract and validate title and site (if specified) from a target string
	 * @param string $target
	 * @return array Contains an 'errors' key for an array of errors if the string is invalid
	 */
	public static function extractTarget( $target ) {
		global $wgCanonicalServer, $wgAllowGlobalMessaging;

		$target = trim( $target );
		$delimiterPos = strrpos( $target, '@' );
		if ( $delimiterPos !== false && $delimiterPos < strlen( $target ) ) {
			$titleText = substr( $target, 0, $delimiterPos );
			$site = strtolower( substr( $target, $delimiterPos + 1 ) );
		} else {
			$titleText = $target;
			$site = null;
		}

		$result = [];

		$title = Title::newFromText( $titleText );
		if ( !$title
			|| $title->getText() === ''
			|| !$title->canExist()
		) {
			$result['errors'][] = 'invalidtitle';
		} else {
			$result['title'] = $title->getPrefixedText(); // Use the canonical form.
		}

		if ( $site !== null && $site !== UrlHelper::getBaseUrl( $wgCanonicalServer ) ) {
			if ( !$wgAllowGlobalMessaging || DatabaseLookup::getDBName( $site ) === null ) {
				$result['errors'][] = 'invalidsite';
			} else {
				$result['site'] = $site;
			}
		} elseif ( $title && $title->isExternal() ) {
			// Target has site set to current wiki, but external title
			// TODO: Provide better error message?
			$result['errors'][] = 'invalidtitle';
		}

		return $result;
	}
}

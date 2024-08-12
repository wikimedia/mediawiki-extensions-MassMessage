<?php

namespace MediaWiki\MassMessage\Lookup;

use MediaWiki\MainConfigNames;
use MediaWiki\MassMessage\Content\MassMessageListContent;
use MediaWiki\MassMessage\UrlHelper;
use MediaWiki\MediaWikiServices;
use MediaWiki\Revision\SlotRecord;
use MediaWiki\Title\Title;
use MediaWiki\WikiMap\WikiMap;

class ListContentSpamlistLookup extends SpamlistLookup {

	/**
	 * @var Title
	 */
	protected $spamlist;

	/**
	 * @param Title $spamlist
	 */
	public function __construct( Title $spamlist ) {
		$this->spamlist = $spamlist;
	}

	/**
	 * Get an array of targets from a page with the MassMessageListContent model.
	 *
	 * @return array[]
	 */
	public function fetchTargets() {
		$services = MediaWikiServices::getInstance();

		$content = $services
			->getRevisionLookup()
			->getRevisionByTitle( $this->spamlist )
			->getContent( SlotRecord::MAIN );
		'@phan-var MassMessageListContent $content';
		$targets = $content->getValidTargets();
		$currentWikiId = WikiMap::getCurrentWikiId();
		foreach ( $targets as &$target ) {
			if ( array_key_exists( 'site', $target ) ) {
				$target['wiki'] = DatabaseLookup::getDBName( $target['site'] );
			} else {
				$target['site'] = UrlHelper::getBaseUrl(
					$services->getMainConfig()->get( MainConfigNames::CanonicalServer )
				);
				$target['wiki'] = $currentWikiId;
			}
		}
		return $targets;
	}
}

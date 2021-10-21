<?php

namespace MediaWiki\MassMessage\Lookup;

use MediaWiki\MassMessage\UrlHelper;
use MediaWiki\MediaWikiServices;
use MediaWiki\Revision\SlotRecord;
use Title;
use WikiMap;

class ListContentSpamlistLookup extends SpamlistLookup {

	/**
	 * @var Title
	 */
	protected $spamlist;

	public function __construct( Title $spamlist ) {
		$this->spamlist = $spamlist;
	}

	/**
	 * Get an array of targets from a page with the MassMessageListContent model.
	 *
	 * @return array[]
	 */
	public function fetchTargets() {
		global $wgCanonicalServer;

		$targets = MediaWikiServices::getInstance()
			->getRevisionLookup()
			->getRevisionByTitle( $this->spamlist )
			->getContent( SlotRecord::MAIN )
			->getValidTargets();
		$currentWikiId = WikiMap::getCurrentWikiId();
		foreach ( $targets as &$target ) {
			if ( array_key_exists( 'site', $target ) ) {
				$target['wiki'] = DatabaseLookup::getDBName( $target['site'] );
			} else {
				$target['site'] = UrlHelper::getBaseUrl( $wgCanonicalServer );
				$target['wiki'] = $currentWikiId;
			}
		}
		return $targets;
	}
}

<?php

namespace MediaWiki\MassMessage\Lookup;

use Category;
use MediaWiki\MassMessage\UrlHelper;
use Title;
use WikiMap;

class CategorySpamlistLookup extends SpamlistLookup {

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
	 * Get an array of targets from a category
	 * @return array[]
	 */
	public function fetchTargets() {
		global $wgCanonicalServer;

		$members = Category::newFromTitle( $this->spamlist )->getMembers();
		$targets = [];
		$currentWikiId = WikiMap::getCurrentWikiId();

		/** @var Title $member */
		foreach ( $members as $member ) {
			$targets[] = [
				'title' => $member->getPrefixedText(),
				'wiki' => $currentWikiId,
				'site' => UrlHelper::getBaseUrl( $wgCanonicalServer ),
			];
		}
		return $targets;
	}

	/**
	 * @return false
	 */
	public function isCachable() {
		return false;
	}
}

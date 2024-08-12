<?php

namespace MediaWiki\MassMessage\Lookup;

use MediaWiki\Category\Category;
use MediaWiki\MainConfigNames;
use MediaWiki\MassMessage\UrlHelper;
use MediaWiki\MediaWikiServices;
use MediaWiki\Title\Title;
use MediaWiki\WikiMap\WikiMap;

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
		$members = Category::newFromTitle( $this->spamlist )->getMembers();
		$targets = [];
		$currentWikiId = WikiMap::getCurrentWikiId();

		/** @var Title $member */
		foreach ( $members as $member ) {
			$targets[] = [
				'title' => $member->getPrefixedText(),
				'wiki' => $currentWikiId,
				'site' => UrlHelper::getBaseUrl(
					MediaWikiServices::getInstance()->getMainConfig()->get( MainConfigNames::CanonicalServer )
				),
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

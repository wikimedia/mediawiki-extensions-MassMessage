<?php

namespace MediaWiki\MassMessage;

use Category;
use Title;

class CategorySpamlistLookup extends SpamlistLookup {

	/**
	 * @var Title
	 */
	protected $spamlist;

	public function __construct( Title $spamlist ) {
		$this->spamlist = $spamlist;
	}
	/**
	 * Get an array of targets from a category
	 * @return array
	 */
	public function fetchTargets() {
		global $wgCanonicalServer;

		$members = Category::newFromTitle( $this->spamlist )->getMembers();
		$targets = [];

		/** @var Title $member */
		foreach ( $members as $member ) {
			$targets[] = [
				'title' => $member->getPrefixedText(),
				'wiki' => wfWikiID(),
				'site' => UrlHelper::getBaseUrl( $wgCanonicalServer ),
			];
		}
		return $targets;
	}

	/**
	 * Returns False
	 * @return Bool
	 */
	public function isCachable() {
		return false;
	}
}

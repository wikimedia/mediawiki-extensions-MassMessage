<?php

/**
 * Abstract test case containing setup code and common functions
 */

abstract class MassMessageTestCase extends MediaWikiTestCase {

	protected function setUp() {
		global $wgLqtPages, $wgContLang;
		parent::setUp();
		$wgConf = new SiteConfiguration;
		$wgConf->wikis = [ 'enwiki', 'dewiki', 'frwiki' ];
		$wgConf->suffixes = [ 'wiki' ];
		$wgConf->settings = [
			'wgServer' => [
				'enwiki' => '//en.wikipedia.org',
				'dewiki' => '//de.wikipedia.org',
				'frwiki' => '//fr.wikipedia.org',
			],
			'wgCanonicalServer' => [
				'enwiki' => 'https://en.wikipedia.org',
				'dewiki' => 'https://de.wikipedia.org',
				'frwiki' => 'https://fr.wikipedia.org',
			],
			'wgArticlePath' => [
				'default' => '/wiki/$1',
			],
		];
		$this->setMwGlobals( 'wgConf', $wgConf );
		$proj = $wgContLang->getFormattedNsText( NS_PROJECT );
		$wgLqtPages[] = $proj . ':LQT test';
		// Create a redirect
		$r = Title::newFromText( 'User talk:Redirect target' );
		self::updatePage( $r, 'blank' );
		$r2 = Title::newFromText( 'User talk:Is a redirect' );
		self::updatePage( $r2, '#REDIRECT [[User talk:Redirect target]]' );
	}

	/**
	 * Updates $title with the provided $text
	 * @param Title title
	 * @param string $text
	 */
	public static function updatePage( $title, $text ) {
		$user = new User();
		$page = WikiPage::factory( $title );
		$content = ContentHandler::makeContent( $text, $page->getTitle() );
		$page->doEditContent( $content, "summary", 0, false, $user );
	}
}

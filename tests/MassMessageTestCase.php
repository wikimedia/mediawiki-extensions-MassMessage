<?php

/**
 * Abstract test case containing setup code and common functions
 */

abstract class MassMessageTestCase extends MediaWikiTestCase {

	protected function setUp() {
		// $wgConf ewwwww
		global $wgConf, $wgLocalDatabases, $wgLqtPages, $wgContLang;
		$wgConf = new SiteConfiguration;
		$wgConf->wikis = array( 'enwiki', 'dewiki', 'frwiki', 'wiki' );
		$wgConf->suffixes = array( 'wiki' );
		$wgConf->settings = array(
			'wgServer' => array(
				'enwiki' => '//en.wikipedia.org',
				'dewiki' => '//de.wikipedia.org',
				'frwiki' => '//fr.wikipedia.org',
			),
		);
		$wgLocalDatabases =& $wgConf->getLocalDatabases();
		$proj = $wgContLang->getFormattedNsText( NS_PROJECT );
		$wgLqtPages[] = $proj . ':LQT test';
		// Create a redirect
		$r = Title::newFromText( 'User talk:Redirect target' );
		self::updatePage( $r, 'blank' );
		$r2 = Title::newFromText( 'User talk:Is a redirect' );
		self::updatePage( $r2, '#REDIRECT [[User talk:Redirect target]]' );
		parent::setUp();
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

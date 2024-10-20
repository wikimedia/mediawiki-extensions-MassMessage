<?php

namespace MediaWiki\MassMessage;

use MediaWiki\Config\SiteConfiguration;
use MediaWiki\Content\ContentHandler;
use MediaWiki\MediaWikiServices;
use MediaWiki\Title\Title;
use MediaWikiIntegrationTestCase;

/**
 * Abstract test case containing setup code and common functions
 */
abstract class MassMessageTestCase extends MediaWikiIntegrationTestCase {

	protected function setUp(): void {
		global $wgLqtPages;
		parent::setUp();
		$conf = new SiteConfiguration();
		$conf->wikis = [ 'enwiki', 'dewiki', 'frwiki' ];
		$conf->suffixes = [ 'wiki' ];
		$conf->settings = [
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
		$this->setMwGlobals( 'wgConf', $conf );
		$proj = MediaWikiServices::getInstance()->getContentLanguage()
			->getFormattedNsText( NS_PROJECT );
		$wgLqtPages[] = $proj . ':LQT test';
		// Create a redirect
		$r = Title::makeTitle( NS_USER_TALK, 'Redirect target' );
		$this->updatePage( $r, 'blank' );
		$r2 = Title::makeTitle( NS_USER_TALK, 'Is a redirect' );
		$this->updatePage( $r2, '#REDIRECT [[User talk:Redirect target]]' );
	}

	/**
	 * Updates $title with the provided $text
	 * @param Title $title
	 * @param string $text
	 */
	public function updatePage( $title, $text ) {
		$page = MediaWikiServices::getInstance()->getWikiPageFactory()->newFromTitle( $title );
		$content = ContentHandler::makeContent( $text, $page->getTitle() );
		$page->doUserEditContent( $content, $this->getTestUser()->getUser(), "summary" );
	}
}

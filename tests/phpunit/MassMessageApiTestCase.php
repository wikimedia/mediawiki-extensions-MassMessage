<?php

namespace MediaWiki\MassMessage;

use MediaWiki\Config\SiteConfiguration;
use MediaWiki\Tests\Api\ApiTestCase;

/**
 * Abstract test case containing setup code and common functions
 */
abstract class MassMessageApiTestCase extends ApiTestCase {

	protected function setUp(): void {
		parent::setUp();
		$conf = new SiteConfiguration;
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
	}
}

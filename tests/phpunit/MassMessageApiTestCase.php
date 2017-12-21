<?php
namespace MediaWiki\MassMessage;

use ApiTestCase;
use SiteConfiguration;

/**
 * Abstract test case containing setup code and common functions
 */

abstract class MassMessageApiTestCase extends ApiTestCase {

	public static function setUpBeforeClass() {
		// $wgConf ewwwww
		global $wgConf, $wgLocalDatabases;
		parent::setUpBeforeClass();
		$wgConf = new SiteConfiguration;
		$wgConf->wikis = [ 'enwiki', 'dewiki', 'frwiki', 'wiki' ];
		$wgConf->suffixes = [ 'wiki' ];
		$wgConf->settings = [
			'wgServer' => [
				'enwiki' => '//en.wikipedia.org',
				'dewiki' => '//de.wikipedia.org',
				'frwiki' => '//fr.wikipedia.org',
			],
		];
		$wgLocalDatabases =& $wgConf->getLocalDatabases();
	}
}

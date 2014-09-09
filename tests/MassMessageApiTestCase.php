<?php

/**
 * Abstract test case containing setup code and common functions
 */

abstract class MassMessageApiTestCase extends ApiTestCase {

	public static function setUpBeforeClass() {
		// $wgConf ewwwww
		global $wgConf, $wgLocalDatabases;
		parent::setUpBeforeClass();
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
	}
}

<?php

namespace MediaWiki\MassMessage;

use MediaWiki\Tests\Api\ApiTestCase;

/**
 * Abstract test case containing setup code and common functions
 */
abstract class MassMessageApiTestCase extends ApiTestCase {

	protected function setUp(): void {
		parent::setUp();
		$this->setMwGlobals( 'wgConf', MassMessageTestCase::getTestSiteConfiguration() );
	}
}

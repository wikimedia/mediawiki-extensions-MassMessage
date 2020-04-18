<?php

namespace MediaWiki\MassMessage;

/**
 * Tests for the API module to serve autocomplete requests for the site field
 * @group API
 * @group medium
 * @covers \MediaWiki\MassMessage\Api\ApiQueryMMSites
 */
class ApiQueryMMSitesTest extends MassMessageApiTestCase {

	public function testQuery() {
		$result = $this->doApiRequest( [
			'action' => 'query',
			'list' => 'mmsites',
			'term' => 'en'
		] );
		$this->assertArrayHasKey( 'query', $result[0] );

		$this->assertEquals(
			[ 'mmsites' => [ 'en.wikipedia.org' ] ],
			$result[0]['query']
		);
	}
}

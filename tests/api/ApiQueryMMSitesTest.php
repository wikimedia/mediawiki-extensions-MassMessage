<?php

/**
 * Tests for the API module to serve autocomplete requests for the site field
 * @group API
 * @group medium
 */
class ApiQueryMMSitesTest extends MassMessageApiTestCase {

	public function testQuery() {
		$result = $this->doApiRequest( array(
			'action' => 'query',
			'list' => 'mmsites',
			'term' => 'en'
		) );
		$this->assertArrayHasKey( 'query', $result[0] );

		$this->assertEquals(
			array( 'mmsites' => array( 'en.wikipedia.org' ) ),
			$result[0]['query']
		);
	}
}

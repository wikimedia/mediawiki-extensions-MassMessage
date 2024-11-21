<?php

namespace MediaWiki\MassMessage\Lookup;

use MediaWiki\MassMessage\MassMessageTestCase;

/**
 * @group Database
 */
class DatabaseLookupTest extends MassMessageTestCase {

	public static function provideGetDBName() {
		return [
			[ 'en.wikipedia.org', 'enwiki' ],
			[ 'fr.wikipedia.org', 'frwiki' ],
			[ 'de.wikipedia.org', 'dewiki' ],
			[ 'not.a.wiki.known.to.us', null ],
		];
	}

	/**
	 * @covers \MediaWiki\MassMessage\Lookup\DatabaseLookup::getDBName
	 * @dataProvider provideGetDBName
	 * @param string $url
	 * @param string $expected
	 */
	public function testGetDBName( $url, $expected ) {
		$dbname = DatabaseLookup::getDBName( $url );
		$this->assertEquals( $expected, $dbname );
	}

	/**
	 * Integration test for T62075
	 *
	 * @covers \MediaWiki\MassMessage\Lookup\DatabaseLookup::getDBName
	 * @covers \MediaWiki\MassMessage\Lookup\DatabaseLookup::getDatabases
	 */
	public function testCacheInvalidation() {
		global $wgConf;
		// Remove a wiki for adding later
		$wgConf->wikis = array_diff( $wgConf->wikis, [ 'enwiki' ] );
		$this->assertNull( DatabaseLookup::getDBName( 'en.wikipedia.org' ) );

		// Confirm basic cache operation
		$wgConf->settings['wgCanonicalServer']['frwiki'] = 'https://foo.wikipedia.org';
		$this->assertSame( 'frwiki',
			DatabaseLookup::getDBName( 'fr.wikipedia.org' ) );

		// Add a wiki
		$wgConf->wikis[] = 'enwiki';

		// Confirm cache invalidation
		$this->assertSame( 'enwiki',
			DatabaseLookup::getDBName( 'en.wikipedia.org' ) );
		$this->assertSame( 'frwiki',
			DatabaseLookup::getDBName( 'foo.wikipedia.org' ) );
	}
}

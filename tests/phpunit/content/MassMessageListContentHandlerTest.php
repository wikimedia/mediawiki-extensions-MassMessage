<?php

namespace MediaWiki\MassMessage;

use MediaWiki\MainConfigNames;
use MediaWiki\MassMessage\Content\MassMessageListContentHandler;
use MediaWiki\Revision\SlotRecord;
use MediaWiki\Title\Title;

/**
 * This inherits ApiTestCase because MassMessageListContentHandler::edit goes through
 * the Edit API.
 * @group API
 * @group Database
 * @group medium
 * @covers \MediaWiki\MassMessage\Content\MassMessageListContentHandler
 */
class MassMessageListContentHandlerTest extends MassMessageApiTestCase {

	/** @var string */
	protected static $spamlist = 'MassMessageListCHTest_spamlist';

	protected function setUp(): void {
		parent::setUp();
		$this->setGroupPermissions( '*', 'createwithcontentmodel', true );
		$this->mergeMwGlobalArrayValue(
			'wgMassMessageWikiAliases', [
				'en.wikipedia.org' => 'enwiki',
				'de.wikipedia.org' => 'dewiki'
			]
		);
		$this->overrideMwServices();
	}

	/**
	 * Return the sign of $par.
	 * @param int $par
	 * @return int
	 */
	protected static function getSign( $par ) {
		return ( $par > 0 ) - ( $par < 0 );
	}

	public function testEdit() {
		$title = Title::makeTitle( NS_MAIN, self::$spamlist );
		$targets = [
			[ 'title' => 'A' ],
			[ 'title' => 'B', 'site' => 'en.wikipedia.org' ]
		];
		$result = MassMessageListContentHandler::edit(
			$title,
			'description',
			$targets,
			'summary',
			false,
			'preferences',
			$this->apiContext
		);
		$this->assertStatusGood( $result );
		$rev = $this->getServiceContainer()->getRevisionLookup()->getRevisionByTitle( $title );
		$content = $rev->getContent( SlotRecord::MAIN );
		$this->assertEquals( 'description', $content->getDescription() );
		$this->assertEquals( $targets, $content->getTargets() );
		$this->assertEquals( 'summary', $rev->getComment()->text );
	}

	public function testInvalidEdit() {
		$title = Title::makeTitle( NS_MAIN, self::$spamlist );
		$result = MassMessageListContentHandler::edit(
			$title,
			'description',
			'not a target array',
			'summary',
			false,
			'preferences',
			$this->apiContext
		);
		$this->assertStatusNotOK( $result );
	}

	public function testNormalizeTargetArray() {
		$input = [
			[ 'title' => 'A', 'site' => 'en.wikipedia.org' ],
			[ 'title' => 'B', 'site' => 'de.wikipedia.org' ],
			[ 'title' => 'D' ],
			[ 'title' => 'C' ],
			[ 'title' => 'A', 'site' => 'en.wikipedia.org' ]
		];
		$expected = [
			[ 'title' => 'C' ],
			[ 'title' => 'D' ],
			[ 'title' => 'B', 'site' => 'de.wikipedia.org' ],
			[ 'title' => 'A', 'site' => 'en.wikipedia.org' ]
		];
		$this->assertEquals(
			$expected,
			MassMessageListContentHandler::normalizeTargetArray( $input )
		);
	}

	public static function provideCompareTargets() {
		return [
			[
				[ 'title' => 'A' ],
				[ 'title' => 'B' ],
			-1 ],
			[
				[ 'title' => 'A' ],
				[ 'title' => 'A' ],
			0 ],
			[
				[ 'title' => 'A', 'site' => 'en.wikipedia.org' ],
				[ 'title' => 'A', 'site' => 'en.wikipedia.org' ],
			0 ],
			[
				[ 'title' => 'A', 'site' => 'en.wikipedia.org' ],
				[ 'title' => 'B', 'site' => 'en.wikipedia.org' ],
			-1 ],
			[
				[ 'title' => 'A', 'site' => 'en.wikipedia.org' ],
				[ 'title' => 'B' ],
			1 ],
			[
				[ 'title' => 'A', 'site' => 'en.wikipedia.org' ],
				[ 'title' => 'B', 'site' => 'de.wikipedia.org' ],
			1 ]
		];
	}

	/**
	 * @dataProvider provideCompareTargets
	 * @param array $a
	 * @param array $b
	 * @param int $expected
	 */
	public function testCompareTargets( $a, $b, $expected ) {
		$this->assertEquals(
			$expected,
			self::getSign( MassMessageListContentHandler::compareTargets( $a, $b ) )
		);
	}

	public static function provideExtractTarget() {
		return [
			[ 'A', [ 'title' => 'A' ] ],
			[ 'A@test.wikipedia.org', [ 'title' => 'A' ] ],
			[ 'A@domain.org@test.wikipedia.org', [ 'title' => 'A@domain.org' ] ],
			[ 'A@en.wikipedia.org', [ 'title' => 'A', 'site' => 'en.wikipedia.org' ] ],
			[ 'a@EN.WIKIPEDIA.ORG', [ 'title' => 'A', 'site' => 'en.wikipedia.org' ] ],
			[ 'A@domain.org@en.wikipedia.org',
				[ 'title' => 'A@domain.org', 'site' => 'en.wikipedia.org' ] ],
			[ '_', [ 'errors' => [ 'invalidtitle' ] ] ],
			[ 'A@invalid.org', [ 'title' => 'A', 'errors' => [ 'invalidsite' ] ] ],
			[ '_@invalid.org', [ 'errors' => [ 'invalidtitle', 'invalidsite' ] ] ]
		];
	}

	/**
	 * @dataProvider provideExtractTarget
	 * @param string $targetString
	 * @param array $expected Parsed target
	 */
	public function testExtractTarget( $targetString, $expected ) {
		// Temporarily set $wgCanonicalServer for this test so its value is predictable.
		$this->overrideConfigValue( MainConfigNames::CanonicalServer, 'http://test.wikipedia.org' );
		$this->assertEquals(
			$expected,
			MassMessageListContentHandler::extractTarget( $targetString )
		);
	}
}

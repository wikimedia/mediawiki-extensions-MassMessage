<?php

/**
 * This inherits ApiTestCase because MassMessageListContentHandler::edit goes through
 * the Edit API.
 * @group API
 * @group Database
 * @group medium
 */
class MassMessageListContentHandlerTest extends MassMessageApiTestCase {

	protected static $spamlist = 'MassMessageListCHTest_spamlist';

	public function setUp() {
		parent::setUp();
		$this->mergeMwGlobalArrayValue(
			'wgGroupPermissions', [ '*' => [ 'editcontentmodel' => true ] ]
		);
	}

	/**
	 * Return the sign of $par.
	 * @param int $par
	 */
	protected static function getSign( $par ) {
		return ( $par > 0 ) - ( $par < 0 );
	}

	/**
	 * @covers MassMessageListContentHandler::edit
	 */
	public function testEdit() {
		$this->doLogin();
		$title = Title::newFromText( self::$spamlist );
		$targets = [
			[ 'title' => 'A' ],
			[ 'title' => 'B','site' => 'en.wikipedia.org' ]
		];
		$result = MassMessageListContentHandler::edit(
			$title,
			'description',
			$targets,
			'summary',
			RequestContext::getMain()
		);
		$this->assertTrue( $result->isGood() );
		$rev = Revision::newFromTitle( $title );
		$content = $rev->getContent();
		$this->assertEquals( 'description', $content->getDescription() );
		$this->assertEquals( $targets, $content->getTargets() );
		$this->assertEquals( 'summary', $rev->getComment() );
	}

	/**
	 * @covers MassMessageListContentHandler::edit
	 */
	public function testInvalidEdit() {
		$this->doLogin();
		$title = Title::newFromText( self::$spamlist );
		$result = MassMessageListContentHandler::edit(
			$title,
			'description',
			'not a target array',
			'summary',
			new RequestContext
		);
		$this->assertFalse( $result->isGood() );
	}

	/**
	 * @covers MassMessageListContentHandler::normalizeTargetArray
	 */
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
	 * @covers MassMessageListContentHandler::compareTargets
	 * @dataProvider provideCompareTargets
	 * @param array $a
	 * @param array $b
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
	 * @covers MassMessageListContentHandler::extractTarget
	 * @dataProvider provideExtractTarget
	 * @param string $targetString
	 * @param array $expected Parsed target
	 */
	public function testExtractTarget( $targetString, $expected ) {
		// Temporarily set $wgCanonicalServer for this test so its value is predictable.
		$this->setMwGlobals( 'wgCanonicalServer', 'http://test.wikipedia.org' );
		$this->assertEquals(
			$expected,
			MassMessageListContentHandler::extractTarget( $targetString )
		);
	}
}
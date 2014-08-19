<?php

/**
 * This inherits ApiTestCase because MassMessageListContentHandler::edit goes through
 * the Edit API.
 * @group medium
 */
class MassMessageListContentHandlerTest extends MassMessageApiTestCase {

	protected static $spamlist = 'MassMessageListCHTest_spamlist';

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
		$targets = array(
			array( 'title' => 'A' ),
			array( 'title' => 'B','site' => 'en.wikipedia.org' )
		);
		$result = MassMessageListContentHandler::edit(
			$title,
			'description',
			$targets,
			'summary',
			new RequestContext
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
		$input = array(
			array( 'title' => 'A', 'site' => 'en.wikipedia.org' ),
			array( 'title' => 'B', 'site' => 'de.wikipedia.org' ),
			array( 'title' => 'D' ),
			array( 'title' => 'C' ),
			array( 'title' => 'A', 'site' => 'en.wikipedia.org' )
		);
		$expected = array(
			array( 'title' => 'C' ),
			array( 'title' => 'D' ),
			array( 'title' => 'B', 'site' => 'de.wikipedia.org' ),
			array( 'title' => 'A', 'site' => 'en.wikipedia.org' )
		);
		$this->assertEquals(
			$expected,
			MassMessageListContentHandler::normalizeTargetArray( $input )
		);
	}

	public static function provideCompareTargets() {
		return array(
			array(
				array( 'title' => 'A' ),
				array( 'title' => 'B' ),
			-1 ),
			array(
				array( 'title' => 'A' ),
				array( 'title' => 'A' ),
			0 ),
			array(
				array( 'title' => 'A', 'site' => 'en.wikipedia.org' ),
				array( 'title' => 'A', 'site' => 'en.wikipedia.org' ),
			0 ),
			array(
				array( 'title' => 'A', 'site' => 'en.wikipedia.org' ),
				array( 'title' => 'B', 'site' => 'en.wikipedia.org' ),
			-1 ),
			array(
				array( 'title' => 'A', 'site' => 'en.wikipedia.org' ),
				array( 'title' => 'B' ),
			1 ),
			array(
				array( 'title' => 'A', 'site' => 'en.wikipedia.org' ),
				array( 'title' => 'B', 'site' => 'de.wikipedia.org' ),
			1 )
		);
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
		return array(
			array( 'A', array( 'title' => 'A' ) ),
			array( 'A@test.wikipedia.org', array( 'title' => 'A' ) ),
			array( 'A@domain.org@test.wikipedia.org', array( 'title' => 'A@domain.org' ) ),
			array( 'A@en.wikipedia.org', array( 'title' => 'A', 'site' => 'en.wikipedia.org' ) ),
			array( 'a@EN.WIKIPEDIA.ORG', array( 'title' => 'A', 'site' => 'en.wikipedia.org' ) ),
			array( 'A@domain.org@en.wikipedia.org',
				array( 'title' => 'A@domain.org', 'site' => 'en.wikipedia.org' ) ),
			array( '_', array( 'errors' => array('invalidtitle' ) ) ),
			array( 'A@invalid.org', array( 'title' => 'A', 'errors' => array( 'invalidsite' ) ) ),
			array( '_@invalid.org', array( 'errors' => array( 'invalidtitle', 'invalidsite' ) ) )
		);
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

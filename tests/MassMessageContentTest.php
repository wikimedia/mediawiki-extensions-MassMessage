<?php

class MassMessageListContentTest extends MassMessageTestCase {

	public static function provideIsValid() {
		return array(
			array( '{}', false ),
			array( '{"description":"","targets":""}', false ),
			array( '{"description":"","targets":[]}', true ),
			array( '{"description":"","targets":["A"]}', false ),
			array( '{"description":"foo","targets":[{"title":"A"}]}', true ),
			array( '{"description":"foo","targets":[{"title":"_"}]}', false )
		);
	}

	/**
	 * @covers MassMessageListContent::isValid
	 * @dataProvider provideIsValid
	 * @param string $text
	 * @param bool $expected
	 */
	public function testIsValid( $text, $expected ) {
		$content = new MassMessageListContent( $text );
		$this->assertEquals( $expected, $content->isValid() );
	}

	public static function provideHasInvalidTargets() {
		return array(
			array( '{"description":"","targets":[{"title":"A"}]}', false ),
			array( '{"description":"","targets":[{"title":"A","site":"en.wikipedia.org"}]}',
				false ),
			array( '{"description":"","targets":[{"title":"A","site":"invalid.org"}]}', true )
		);
	}

	/**
	 * @covers MassMessageListContent::hasInvalidTargets
	 * @dataProvider provideHasInvalidTargets
	 * @param string $text
	 * @param bool $expected
	 */
	public function testHasInvalidTargets( $text, $expected ) {
		$content = new MassMessageListContent( $text );
		$this->assertEquals( $expected, $content->hasInvalidTargets() );
	}

	/**
	 * @covers MassMessageListContent::getDescription
	 */
	public function testGetDescription() {
		$content = new MassMessageListContent( '{"description":"foo","targets":[]}' );
		$this->assertEquals( 'foo', $content->getDescription() );
	}

	/**
	 * @covers MassMessageListContent::getTargets
	 */
	public function testGetTargets() {
		$text = '{"description":"","targets":['
			. '{"title":"A"},'
			. '{"title":"B","site":"en.wikipedia.org"}'
			. ']}';
		$content = new MassMessageListContent( $text );
		$expected = array(
			array( 'title' => 'A' ),
			array( 'title' => 'B', 'site' => 'en.wikipedia.org' )
		);
		$this->assertEquals( $expected, $content->getTargets() );
	}

	/**
	 * @covers MassMessageListContent::getValidTargets
	 */
	public function testGetValidTargets() {
		$text = '{"description":"","targets":['
			. '{"title":"A"},'
			. '{"title":"B","site":"en.wikipedia.org"},'
			. '{"title":"C","site":"invalid.org"}'
			. ']}';
		$content = new MassMessageListContent( $text );
		$expected = array(
			array( 'title' => 'A' ),
			array( 'title' => 'B', 'site' => 'en.wikipedia.org' )
		);
		$this->assertEquals( $expected, $content->getValidTargets() );
	}

	/**
	 * @covers MassMessageListContent::getTargetStrings
	 */
	public function testGetTargetStrings() {
		// Temporarily set $wgCanonicalServer for this test so its value is predictable.
		$this->setMwGlobals( 'wgCanonicalServer', 'http://test.wikipedia.org' );
		$text = '{"description":"","targets":['
			. '{"title":"User talk:A"},'
			. '{"title":"User talk:B@en.wikipedia.org"},'
			. '{"title":"User talk:C","site":"en.wikipedia.org"}'
			. ']}';
		$content = new MassMessageListContent( $text );
		$expected = array(
			'User talk:A',
			'User talk:B@en.wikipedia.org@test.wikipedia.org',
			'User talk:C@en.wikipedia.org'
		);
		$this->assertEquals( $expected, $content->getTargetStrings() );
	}
}

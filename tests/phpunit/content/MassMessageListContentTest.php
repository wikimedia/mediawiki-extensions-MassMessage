<?php

namespace MediaWiki\MassMessage;

use MediaWiki\MainConfigNames;
use MediaWiki\MassMessage\Content\MassMessageListContent;

/**
 * @group Database
 */
class MassMessageListContentTest extends MassMessageTestCase {

	public static function provideIsValid() {
		return [
			[ '{}', false ],
			[ '{"description":"","targets":""}', false ],
			[ '{"description":"","targets":[]}', true ],
			[ '{"description":"","targets":["A"]}', false ],
			[ '{"description":"foo","targets":[{"title":"A"}]}', true ],
			[ '{"description":"foo","targets":[{"title":"_"}]}', false ]
		];
	}

	/**
	 * @covers \MediaWiki\MassMessage\Content\MassMessageListContent::isValid
	 * @dataProvider provideIsValid
	 * @param string $text
	 * @param bool $expected
	 */
	public function testIsValid( $text, $expected ) {
		$content = new MassMessageListContent( $text );
		$this->assertEquals( $expected, $content->isValid() );
	}

	public static function provideHasInvalidTargets() {
		return [
			[ '{"description":"","targets":[{"title":"A"}]}', false ],
			[ '{"description":"","targets":[{"title":"A","site":"en.wikipedia.org"}]}',
				false ],
			[ '{"description":"","targets":[{"title":"A","site":"invalid.org"}]}', true ]
		];
	}

	/**
	 * @covers \MediaWiki\MassMessage\Content\MassMessageListContent::hasInvalidTargets
	 * @dataProvider provideHasInvalidTargets
	 * @param string $text
	 * @param bool $expected
	 */
	public function testHasInvalidTargets( $text, $expected ) {
		$content = new MassMessageListContent( $text );
		$this->assertEquals( $expected, $content->hasInvalidTargets() );
	}

	/**
	 * @covers \MediaWiki\MassMessage\Content\MassMessageListContent::getDescription
	 */
	public function testGetDescription() {
		$content = new MassMessageListContent( '{"description":"foo","targets":[]}' );
		$this->assertEquals( 'foo', $content->getDescription() );
	}

	/**
	 * @covers \MediaWiki\MassMessage\Content\MassMessageListContent::getTargets
	 */
	public function testGetTargets() {
		$text = '{"description":"","targets":['
			. '{"title":"A"},'
			. '{"title":"B","site":"en.wikipedia.org"}'
			. ']}';
		$content = new MassMessageListContent( $text );
		$expected = [
			[ 'title' => 'A' ],
			[ 'title' => 'B', 'site' => 'en.wikipedia.org' ]
		];
		$this->assertEquals( $expected, $content->getTargets() );
	}

	/**
	 * @covers \MediaWiki\MassMessage\Content\MassMessageListContent::getValidTargets
	 */
	public function testGetValidTargets() {
		$text = '{"description":"","targets":['
			. '{"title":"A"},'
			. '{"title":"B","site":"en.wikipedia.org"},'
			. '{"title":"C","site":"invalid.org"}'
			. ']}';
		$content = new MassMessageListContent( $text );
		$expected = [
			[ 'title' => 'A' ],
			[ 'title' => 'B', 'site' => 'en.wikipedia.org' ]
		];
		$this->assertEquals( $expected, $content->getValidTargets() );
	}

	/**
	 * @covers \MediaWiki\MassMessage\Content\MassMessageListContent::getTargetStrings
	 */
	public function testGetTargetStrings() {
		// Temporarily set $wgCanonicalServer for this test so its value is predictable.
		$this->overrideConfigValue( MainConfigNames::CanonicalServer, 'http://test.wikipedia.org' );
		$text = '{"description":"","targets":['
			. '{"title":"User talk:A"},'
			. '{"title":"User talk:B@en.wikipedia.org"},'
			. '{"title":"User talk:C","site":"en.wikipedia.org"}'
			. ']}';
		$content = new MassMessageListContent( $text );
		$expected = [
			'User talk:A',
			'User talk:B@en.wikipedia.org@test.wikipedia.org',
			'User talk:C@en.wikipedia.org'
		];
		$this->assertEquals( $expected, $content->getTargetStrings() );
	}
}

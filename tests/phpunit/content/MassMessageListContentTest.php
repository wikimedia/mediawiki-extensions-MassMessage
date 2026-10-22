<?php

namespace MediaWiki\MassMessage;

use MediaWiki\MainConfigNames;
use MediaWiki\MassMessage\Content\MassMessageListContent;

/**
 * @group Database
 * @covers \MediaWiki\MassMessage\Content\MassMessageListContent
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
	 * @dataProvider provideHasInvalidTargets
	 * @param string $text
	 * @param bool $expected
	 */
	public function testHasInvalidTargets( $text, $expected ) {
		$content = new MassMessageListContent( $text );
		$this->assertEquals( $expected, $content->hasInvalidTargets() );
	}

	public function testGetDescription() {
		$content = new MassMessageListContent( '{"description":"foo","targets":[]}' );
		$this->assertEquals( 'foo', $content->getDescription() );
	}

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

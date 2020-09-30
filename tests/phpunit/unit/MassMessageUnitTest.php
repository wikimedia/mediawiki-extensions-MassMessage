<?php

namespace MediaWiki\MassMessage;

use MediaWikiUnitTestCase;

class MassMessageUnitTest extends MediaWikiUnitTestCase {
	/**
	 * @covers \MediaWiki\MassMessage\MassMessage::getLabeledSectionContent
	 * @dataProvider provideGetLabeledSectionContent
	 */
	public function testGetLabeledSectionContent( $text, $label, $expectedGood, $expectedBad ) {
		$status = MassMessage::getLabeledSectionContent( $text, $label );

		if ( $expectedGood !== null ) {
			$this->assertTrue( $status->isOK() );
			$this->assertEquals( $expectedGood, $status->getValue() );
		}

		if ( $expectedBad !== null ) {
			$this->assertFalse( $status->isOK() );
		}
	}

	public static function provideGetLabeledSectionContent() {
		yield [
			'a <section begin=x />b<section end=x /> c',
			'x',
			'b',
			null
		];

		yield [
			'a <section begin=x />b<section end=x /> c',
			'y',
			null,
			true
		];

		yield [
			"a <section begin=x />\nb\n<section end=x /> c\n<section begin = x/>d\ne<section end = x/> f",
			'x',
			"b\nd\ne",
			null
		];
	}

	/**
	 * @covers \MediaWiki\MassMessage\MassMessage::getLabeledSections
	 * @dataProvider provideGetLabeledSections
	 */
	public function testGetLabeledSections( $text, $expected ) {
		$actual = MassMessage::getLabeledSections( $text );
		$this->assertArrayEquals( $expected, $actual );
	}

	public function provideGetLabeledSections() {
		yield [
			'a <section begin=x />b<section end=x /> c',
			[ 'x' ],
		];

		yield [
			'a <section begin=x />b<section end=x /> c <section begin = x/>d<section end = x/> e',
			[ 'x' ],
		];

		yield [
			'a <section begin=x />b<section end=x /> c <section begin = y/>d<section end = y/> e',
			[ 'x', 'y' ],
		];
	}
}

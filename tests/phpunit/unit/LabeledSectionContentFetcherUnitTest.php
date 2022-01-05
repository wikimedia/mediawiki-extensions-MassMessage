<?php

namespace MediaWiki\MassMessage\MessageContentFetcher;

use MediaWiki\MassMessage\LanguageAwareText;
use MediaWikiUnitTestCase;

class LabeledSectionContentFetcherUnitTest extends MediaWikiUnitTestCase {
	/**
	 * @covers \MediaWiki\MassMessage\MessageContentFetcher\LabeledSectionContentFetcher::getContent
	 * @dataProvider provideGetLabeledSectionContent
	 */
	public function testGetLabeledSectionContent( $text, $label, $expectedGood, $expectedBad ) {
		$content = new LanguageAwareText( $text, 'en', 'ltr' );
		$labeledSectionContentFetcher = new LabeledSectionContentFetcher();

		$status = $labeledSectionContentFetcher->getContent( $content, $label );

		if ( $expectedGood !== null ) {
			$this->assertTrue( $status->isOK() );
			$this->assertEquals( $expectedGood, $status->getValue()->getWikitext() );
		}

		if ( $expectedBad !== null ) {
			$this->assertFalse( $status->isOK() );
		}
	}

	public static function provideGetLabeledSectionContent() {
		yield "basic syntax" => [
			'a <section begin=x />b<section end=x /> c',
			'x',
			'<section begin=x />b<section end=x />',
			null
		];

		yield "non-existings section" => [
			'a <section begin=x />b<section end=x /> c',
			'y',
			null,
			true
		];

		yield "multiple section tags" => [
			"a <section begin=x />\nb\n<section end=x /> c\n<section begin = x/>d\ne<section end = x/> f",
			'x',
			"<section begin=x />\nb\n<section end=x /><section begin = x/>d\ne<section end = x/>",
			null
		];
	}

	/**
	 * @covers \MediaWiki\MassMessage\MessageContentFetcher\LabeledSectionContentFetcher::getSections
	 * @dataProvider provideGetLabeledSections
	 */
	public function testGetLabeledSections( $text, $expected ) {
		$labeledSectionContentFetcher = new LabeledSectionContentFetcher();
		$actual = $labeledSectionContentFetcher->getSections( $text );
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

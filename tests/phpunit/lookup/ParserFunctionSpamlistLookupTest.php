<?php

namespace MediaWiki\MassMessage\Lookup;

use MediaWiki\MassMessage\MassMessageTestCase;
use MediaWiki\MediaWikiServices;
use MediaWiki\Title\Title;
use MediaWiki\WikiMap\WikiMap;

/**
 * Tests for Parser functions related to target processing
 * @group Database
 */
class ParserFunctionSpamlistLookupTest extends MassMessageTestCase {

	/**
	 * First value is the page text to create
	 * Second is the values we should check in the first array
	 * @return array
	 */
	public static function provideGetParserFunctionTargets() {
		$proj = MediaWikiServices::getInstance()->getContentLanguage()
			// Output changes based on wikiname
			->getFormattedNsText( NS_PROJECT );

		return [
			// project page, no site provided
			[ '{{#target:Project:Example}}', [ 'title' => $proj . ':Example' ], ],
			// user talk page, no site provided
			[ '{{#target:User talk:Example}}', [ 'title' => 'User talk:Example' ], ],
			// local redirect being followed
			[ '{{#target:User talk:Is a redirect}}', [ 'title' => 'User talk:Redirect target' ] ],
			// invalid titles
			[ '{{#target:User:<><}}', [], ],
			[ '{{#target:Project:!!!<><><><>', [], ],
			// project page and site
			[
				'{{#target:Project:Testing|en.wikipedia.org}}',
				[
					'title' => 'Project:Testing',
					'site' => 'en.wikipedia.org',
					'wiki' => 'enwiki'
				],
			],
			// user page and site
			[
				'{{#target:User talk:Test|fr.wikipedia.org}}',
				[
					'title' => 'User talk:Test',
					'site' => 'fr.wikipedia.org',
					'wiki' => 'frwiki'
				],
			],
		];
	}

	/**
	 * @covers \MediaWiki\MassMessage\Lookup\SpamlistLookup::getTargets
	 * @covers \MediaWiki\MassMessage\Lookup\SpamlistLookup::normalizeTargets
	 * @covers \MediaWiki\MassMessage\Lookup\ParserFunctionSpamlistLookup::fetchTargets
	 * @dataProvider provideGetParserFunctionTargets
	 * @param string $text Text of the page to create
	 * @param array $check Stuff to check against
	 */
	public function testGetTargets( $text, $check ) {
		$title = Title::newFromText( 'Input list' );
		$this->updatePage( $title, $text );
		$data = SpamlistLookup::getTargets( $title );

		if ( $check === [] ) {
			// Check that the spamlist is empty
			$this->assertSame( [], $data );
		} else {
			$data = array_values( $data );
			// We're just testing the first value
			$data = $data[0];
			foreach ( $check as $key => $value ) {
				$this->assertEquals( $value, $data[$key] );
			}
			if ( !isset( $check['wiki'] ) ) {
				$this->assertEquals( WikiMap::getCurrentWikiId(), $data['wiki'] );
			}
		}
	}
}

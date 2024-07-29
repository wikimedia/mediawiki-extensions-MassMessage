<?php

namespace MediaWiki\MassMessage;

use MediaWiki\Title\Title;

/**
 * @group Database
 */
class UrlHelperTest extends MassMessageTestCase {

	/**
	 * First parameter is the raw url to parse, second is expected output
	 * @return array
	 */
	public static function provideGetBaseUrl() {
		return [
			[ 'http://en.wikipedia.org', 'en.wikipedia.org' ],
			[ 'https://en.wikipedia.org/wiki/Blah', 'en.wikipedia.org' ],
			[ 'http://en.wikipedia.org:80/wiki/Blah', 'en.wikipedia.org:80' ],
			[ '//test.wikidata.org/wiki/User talk:Example', 'test.wikidata.org' ],
		];
	}

	/**
	 * @covers \MediaWiki\MassMessage\UrlHelper::getBaseUrl
	 * @dataProvider provideGetBaseUrl
	 * @param string $url raw url to parse
	 * @param string $expected expected value
	 */
	public function testGetBaseUrl( $url, $expected ) {
		$output = UrlHelper::getBaseUrl( $url );
		$this->assertEquals( $expected, $output );
	}

	/**
	 * @covers \MediaWiki\MassMessage\UrlHelper::followRedirect
	 */
	public function testFollowRedirect() {
		$title = Title::newfromtext( 'R1' );
		$this->updatePage( $title, '#REDIRECT [[R2]]' );
		$title2 = Title::newfromtext( 'R2' );
		$this->updatePage( $title2, 'foo' );

		$this->assertEquals(
			$title2->getFullText(),
			UrlHelper::followRedirect( $title )->getFullText()
		);
		$this->assertEquals(
			$title2->getFullText(),
			UrlHelper::followRedirect( $title2 )->getFullText()
		);
	}
}

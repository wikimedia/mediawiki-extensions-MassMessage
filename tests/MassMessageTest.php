<?php

class MassMessageTest extends MediaWikiTestCase {
	protected function setUp() {
		parent::setUp();
	}

	protected function tearDown() {
		parent::tearDown();
	}

	/**
	 * Updates $title with the provided $text
	 * @param Title title
	 * @param string $text
	 */
	public static function updatePage( $title, $text ) {
		$user = new User();
		$page = WikiPage::factory( $title );
		$content = ContentHandler::makeContent( $text, $page->getTitle() );
		$page->doEditContent( $content, "summary", 0, false, $user );
	}

	/**
	 * First value is the page text to create
	 * Second is the values we should check in the first array
	 * @return array
	 */
	public static function provideGetParserFunctionTargets() {
		global $wgDBname;

		return array(
			array( '{{#target:User talk:Example}}', array( 'dbname' => $wgDBname, 'title' => 'User talk:Example' ), ),
			array( '{{#target:User:<><}}', array(), ),
		);
	}

	/**
	 * Tests MassMessage::getParserFunctionTargets
	 * @dataProvider provideGetParserFunctionTargets
	 * @param  string $text  Text of the page to create
	 * @param  array $check Stuff to check against
	 */
	public function testGetParserFunctionTargets( $text, $check ) {
		$title = Title::newFromText( 'Input list ');
		self::updatePage( $title, $text );
		$data = MassMessage::getParserFunctionTargets( $title, RequestContext::getMain() );
		if ( empty( $check ) ) {
			// Check that the spamlist is empty
			$this->assertTrue( empty( $data ) );
		} else {
			$data = $data[0]; // We're just testing the first value
			foreach ( $check as $key => $value ) {
				$this->assertEquals( $data[$key], $value );
			}
		}
	}

	/**
	 * First parameter is the raw url to parse, second is expected output
	 * @return array
	 */
	public static function provideGetBaseUrl() {
		return array(
			array( 'http://en.wikipedia.org', 'en.wikipedia.org' ),
			array( 'https://en.wikipedia.org/wiki/Blah', 'en.wikipedia.org' ),
			array( '//test.wikidata.org/wiki/User talk:Example', 'test.wikidata.org' ),
		);
	}

	/**
	 * Tests MassMessage::getBaseUrl
	 * @dataProvider provideGetBaseUrl
	 * @param  string $url      raw url to parse
	 * @param  string $expected expected value
	 */
	public function testGetBaseUrl( $url, $expected ) {
		$output = MassMessage::getBaseUrl( $url );
		$this->assertEquals( $output, $expected );
	}

	/**
	 * Tests MassMessage::followRedirect
	 */
	public function testFollowRedirect() {
		$title = Title::newfromtext( 'R1' );
		self::updatePage( $title, '#REDIRECT [[R2]]' );
		$title2 = Title::newfromtext( 'R2' );
		self::updatePage( $title2, 'foo' );

		$this->assertEquals( $title2->getFullText(), MassMessage::followRedirect( $title )->getFullText() );
		$this->assertEquals( $title2->getFullText(), MassMessage::followRedirect( $title2)->getFullText() );
	}
}

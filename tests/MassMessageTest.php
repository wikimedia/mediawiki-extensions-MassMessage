<?php

class MassMessageTest extends MediaWikiTestCase {

	protected function setUp() {
		parent::setUp();
		$this->title = Title::newfromText( 'Input list' );
		$this->page = WikiPage::factory( $this->title );
	}

	protected function tearDown() {
		parent::tearDown();
	}

	/**
	 * Updates $this->title with the provided text
	 * @param  string $text
	 */
	public static function updatePage( $page, $text ) {
		$user = new User();
		$page->doEdit( $text, "summary", 0, false, $user );
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
		);
	}

	/**
	 * Tests MassMessage::getParserFunctionTargets
	 * @dataProvider provideGetParserFunctionTargets
	 * @param  string $text  Text of the page to create
	 * @param  array $check Stuff to check against
	 */
	public function testGetParserFunctionTargets( $text, $check ) {
		self::updatePage( $this->page, $text );
		$data = MassMessage::getParserFunctionTargets( $this->title, RequestContext::getMain() );
		$data = $data[0]; // We're just testing the first value
		foreach ( $check as $key => $value ) {
			$this->assertEquals( $data[$key], $value );
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
}

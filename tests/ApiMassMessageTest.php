<?php

/**
 * Tests for API module
 * @group API
 * @group medium
 */
class ApiMassMessageTest extends ApiTestCase {

	/**
	 * @var Title
	 */
	static $spamlist = 'Help:ApiMassMessageTest_spamlist';
	static $emptyspamlist = 'Help:ApiMassMessageTest_spamlist2';

	function setUp() {
		global $wgTitle;
		parent::setUp();
		$spamlist = Title::newFromText( self::$spamlist );
		self::updatePage( $spamlist, '{{#target:Project:ApiTest1}}' );
		$emptyspamlist = Title::newFromText( self::$emptyspamlist );
		self::updatePage( $emptyspamlist, 'rawr' );
		$wgTitle = Title::newMainPage(); // So HTMLForm doesn't throw a shit
		$this->doLogin();
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
	 * Tests sending a dummy message
	 * Checks to make sure that the output looks good too
	 */
	function testSending() {
		$apiResult = $this->doApiRequestWithToken( array(
			'action' => 'massmessage',
			'spamlist' => self::$spamlist,
			'message' => 'message',
			'subject' => 'subjectline'
		));

		$apiResult = $apiResult[0];
		$this->assertArrayHasKey( 'massmessage', $apiResult );
		$this->assertArrayHasKey( 'result', $apiResult['massmessage'] );
		$this->assertEquals( 'success', $apiResult['massmessage']['result'] );
		$this->assertArrayHasKey( 'count', $apiResult['massmessage'] );
		$this->assertEquals( 1, $apiResult['massmessage']['count'] );
	}

	/**
	 * Tests that an error is thrown properly for invalid spamlists
	 */
	function testInvalidSpamlist() {
		$this->setExpectedException( 'UsageException', 'The specified page-list page does not exist.' );
		$this->doApiRequestWithToken( array(
			'action' => 'massmessage',
			'spamlist' => '<InvalidPageTitle>',
			'subject' => 'subject',
			'message' => 'msg'
		));
	}

	public static function provideCount() {
		return array(
			array( self::$spamlist, 1 ),
			array( self::$emptyspamlist, 0)
		);
	}

	/**
	 * Tests that the value of 'count' is correct
	 * @dataProvider provideCount
	 * @param $page string of page title
	 * @param $count integer value of what count should be
	 */
	function testCount( $page, $count ) {
		$apiResult = $this->doApiRequestWithToken( array(
			'action' => 'massmessage',
			'spamlist' => $page,
			'message' => 'message',
			'subject' => 'subjectline'
		));
		$this->assertEquals( $count, $apiResult[0]['massmessage']['count'] );
	}

}

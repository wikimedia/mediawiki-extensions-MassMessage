<?php

/**
 * Tests for the API module to send messages
 * @group API
 * @group Database
 * @group medium
 */
class ApiMassMessageTest extends MassMessageApiTestCase {

	protected static $spamlist = 'Help:ApiMassMessageTest_spamlist';
	protected static $emptyspamlist = 'Help:ApiMassMessageTest_spamlist2';

	protected function setUp() {
		parent::setUp();
		$spamlist = Title::newFromText( self::$spamlist );
		self::updatePage( $spamlist, '{{#target:Project:ApiTest1}}' );
		$emptyspamlist = Title::newFromText( self::$emptyspamlist );
		self::updatePage( $emptyspamlist, 'rawr' );
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
	public function testSending() {
		$apiResult = $this->doApiRequestWithToken( [
			'action' => 'massmessage',
			'spamlist' => self::$spamlist,
			'message' => 'message',
			'subject' => 'subjectline'
		] );

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
	public function testInvalidSpamlist() {
		$this->setExpectedException(
			class_exists( ApiUsageException::class ) ? ApiUsageException::class : UsageException::class,
			'The specified delivery list page or category does not exist.'
		);
		$this->doApiRequestWithToken( [
			'action' => 'massmessage',
			'spamlist' => '<InvalidPageTitle>',
			'subject' => 'subject',
			'message' => 'msg'
		] );
	}

	public static function provideCount() {
		return [
			[ self::$spamlist, 1 ],
			[ self::$emptyspamlist, 0 ]
		];
	}

	/**
	 * Tests that the value of 'count' is correct
	 * @dataProvider provideCount
	 * @param string $page string of page title
	 * @param int $count integer value of what count should be
	 */
	public function testCount( $page, $count ) {
		$apiResult = $this->doApiRequestWithToken( [
			'action' => 'massmessage',
			'spamlist' => $page,
			'message' => 'message',
			'subject' => 'subjectline'
		] );
		$this->assertEquals( $count, $apiResult[0]['massmessage']['count'] );
	}

}

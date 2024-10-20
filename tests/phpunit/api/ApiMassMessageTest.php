<?php

namespace MediaWiki\MassMessage;

use MediaWiki\Api\ApiUsageException;
use MediaWiki\Content\ContentHandler;
use MediaWiki\MediaWikiServices;
use MediaWiki\Title\Title;

/**
 * Tests for the API module to send messages.
 * @group API
 * @group Database
 * @group medium
 * @covers \MediaWiki\MassMessage\Api\ApiMassMessage
 */
class ApiMassMessageTest extends MassMessageApiTestCase {

	/** @var string */
	protected static $spamlist = 'Help:ApiMassMessageTest_spamlist';
	/** @var string */
	protected static $emptyspamlist = 'Help:ApiMassMessageTest_spamlist2';
	/** @var string */
	private static $pageMessage = 'Help:Test_Page';

	protected function setUp(): void {
		parent::setUp();
		$spamlist = Title::newFromText( self::$spamlist );
		$this->updatePage( $spamlist, '{{#target:Project:ApiTest1}}' );
		$emptyspamlist = Title::newFromText( self::$emptyspamlist );
		$this->updatePage( $emptyspamlist, 'rawr' );
		$pageMessage = Title::newFromText( self::$pageMessage );
		$this->updatePage( $pageMessage, 'Hello World!' );
	}

	/**
	 * Updates $title with the provided $text
	 * @param Title $title
	 * @param string $text
	 */
	public function updatePage( $title, $text ) {
		$page = MediaWikiServices::getInstance()->getWikiPageFactory()->newFromTitle( $title );
		$content = ContentHandler::makeContent( $text, $page->getTitle() );
		$page->doUserEditContent( $content, $this->getTestUser()->getUser(), "summary" );
	}

	/**
	 * Tests sending a dummy message
	 * Checks to make sure that the output looks good too
	 */
	public function testSending() {
		$sysop = $this->getTestSysop()->getUser();
		$apiResult = $this->doApiRequestWithToken( [
			'action' => 'massmessage',
			'spamlist' => self::$spamlist,
			'message' => 'message',
			'subject' => 'subjectline'
		], null, $sysop );

		$apiResult = $apiResult[0];
		$this->assertArrayHasKey( 'massmessage', $apiResult );
		$this->assertArrayHasKey( 'result', $apiResult['massmessage'] );
		$this->assertEquals( 'success', $apiResult['massmessage']['result'] );
		$this->assertArrayHasKey( 'count', $apiResult['massmessage'] );
		$this->assertSame( 1, $apiResult['massmessage']['count'] );
	}

	/**
	 * Tests that an error is thrown properly for invalid spamlists
	 */
	public function testInvalidSpamlist() {
		$sysop = $this->getTestSysop()->getUser();
		$this->expectException( ApiUsageException::class );
		$this->expectExceptionMessage( 'The specified delivery list page or category does not exist.' );
		$this->doApiRequestWithToken( [
			'action' => 'massmessage',
			'spamlist' => '<InvalidPageTitle>',
			'subject' => 'subject',
			'message' => 'msg'
		], null, $sysop );
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
		$sysop = $this->getTestSysop()->getUser();
		$apiResult = $this->doApiRequestWithToken( [
			'action' => 'massmessage',
			'spamlist' => $page,
			'message' => 'message',
			'subject' => 'subjectline'
		], null, $sysop );
		$this->assertEquals( $count, $apiResult[0]['massmessage']['count'] );
	}

	public function testSendingPage() {
		$sysop = $this->getTestSysop()->getUser();
		$apiResult = $this->doApiRequestWithToken( [
			'action' => 'massmessage',
			'spamlist' => self::$spamlist,
			'message' => 'message',
			'subject' => 'subjectline',
			'page-message' => self::$pageMessage
		], null, $sysop );

		$this->assertSame( 1, $apiResult[0]['massmessage']['count'] );
	}

	public function testSendingInvalidPage() {
		$sysop = $this->getTestSysop()->getUser();
		$page404 = 'Page not found';

		$this->setExpectedApiException( [
			'massmessage-page-message-not-found', $page404
		] );

		$this->doApiRequestWithToken( [
			'action' => 'massmessage',
			'spamlist' => self::$spamlist,
			'message' => 'message',
			'subject' => 'subjectline',
			'page-message' => $page404
		], null, $sysop );
	}

}

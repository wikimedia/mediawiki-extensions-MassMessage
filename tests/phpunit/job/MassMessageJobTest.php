<?php

namespace MediaWiki\MassMessage;

use ContentHandler;
use ExtensionRegistry;
use MediaWiki\MediaWikiServices;
use MWCryptRand;
use RequestContext;
use Revision;
use RuntimeException;
use SpecialPageLanguage;
use Title;
use User;
use WikiMap;
use WikiPage;

class MassMessageJobTest extends MassMessageTestCase {

	/**
	 * Runs a job to edit the given title
	 */
	private function simulateJob( Title $title, array $additionalParams = [] ): array {
		$subject = md5( MWCryptRand::generateHex( 15 ) );
		$params = [
			'subject' => $subject,
			'message' => 'This is a message.',
		];
		$params = array_merge( $params, $additionalParams );
		$params['comment'] = [
			User::newFromName( 'Admin' ),
			'metawiki',
			'http://meta.wikimedia.org/w/index.php?title=Spamlist&oldid=5'
		];
		$job = new MassMessageJob( $title, $params );
		return [ $subject, $job->run() ];
	}

	/**
	 * Returns title with a given string, while making sure that it does not actually exist.
	 *
	 * @param string $titleStr
	 * @return Title
	 */
	private function getTargetTitle( string $titleStr ): Title {
		$target = Title::newFromText( $titleStr );
		if ( $target->exists() ) {
			// Clear it
			$wikipage = WikiPage::factory( $target );
			$wikipage->doDeleteArticleReal( 'reason', $this->getTestSysop()->getUser() );
		}

		return $target;
	}

	private function createPage( string $titleStr, string $pageContent ): Title {
		$title = Title::newFromText( $titleStr );
		$this->createPageByTitle( $title, $pageContent );
		return $title;
	}

	private function createPageByTitle( Title $title, string $pageContent ) {
		$page = WikiPage::factory( $title );
		$content = ContentHandler::makeContent( $pageContent, $title );

		$status = $page->doEditContent(
			$content, __METHOD__, 0, false, $this->getTestSysop()->getUser()
		);

		if ( !$status->isOK() ) {
			throw new RuntimeException(
				'There was an error while creating the page message with title - ' .
				$title->getPrefixedText()
			);
		}
	}

	/**
	 * @covers \MediaWiki\MassMessage\MassMessageJob::sendMessage
	 * @covers \MediaWiki\MassMessage\MassMessageJob::editPage
	 */
	public function testMessageSending() {
		$target = $this->getTargetTitle( 'Project:Testing1234' );
		list( $subj, ) = $this->simulateJob( $target );
		$target = Title::newFromText( 'Project:Testing1234' );
		// Message was created
		$text = WikiPage::factory( $target )->getContent( Revision::RAW )->getNativeData();
		$this->assertEquals(
			"== $subj ==\n\nThis is a message.\n<!-- Message sent by User:Admin@metawiki" .
			" using the list at http://meta.wikimedia.org/w/index.php?title=Spamlist&oldid=5 -->",
			$text
		);
	}

	/**
	 * @covers \MediaWiki\MassMessage\MassMessageJob::addLQTThread
	 * @covers \MediaWiki\MassMessage\MassMessageJob::sendMessage
	 */
	public function testLQTMessageSending() {
		$this->markTestSkipped( 'broken test, T217553' );

		$proj = MediaWikiServices::getInstance()->getContentLanguage()
			->getFormattedNsText( NS_PROJECT ); // Output changes based on wikiname

		if ( !ExtensionRegistry::getInstance()->isLoaded( 'Liquid Threads' ) ) {
			$this->markTestSkipped( "This test requires the LiquidThreads extension" );
		}
		$target = Title::newFromText( 'Project:LQT test' );
		// $this->assertTrue( LqtDispatch::isLqtPage( $target ) );
		// Check that it worked
		list( $subject, ) = $this->simulateJob( $target );
		$this->assertTrue( Title::newFromText( 'Thread:' . $proj . ':LQT test/' . $subject )->exists() );
	}

	/**
	 * @covers \MediaWiki\MassMessage\MassMessageJob::isOptedOut
	 */
	public function testOptOut() {
		$fakejob = new MassMessageJob( Title::newMainPage(), [] );
		$target = Title::newFromText( 'Project:Opt out test page' );
		self::updatePage( $target, '[[Category:Opted-out of message delivery]]' );
		$this->assertTrue( $fakejob->isOptedOut( $target ) );
		$this->assertFalse( $fakejob->isOptedOut(
			Title::newFromText( 'Project:Some random page' )
		) );
		$this->simulateJob( $target ); // Try posting a message to this page
		$text = WikiPage::factory( $target )->getContent( Revision::RAW )->getNativeData();
		// Nothing should be updated.
		$this->assertEquals( '[[Category:Opted-out of message delivery]]', $text );
	}

	/**
	 * @covers \MediaWiki\MassMessage\MassMessageJob::sendMessage
	 */
	public function testPageMessageSending() {
		$pageMessageContent = 'Test page message.';
		$pageMessageTitleStr = 'PageMessage';

		$target = $this->getTargetTitle( 'Project:Testing1234' );
		$pageMessageTitle = $this->createPage( $pageMessageTitleStr, $pageMessageContent );
		$this->simulateJob( $target, [
			'page-message' => $pageMessageTitleStr,
			'pageMessageTitle' => $pageMessageTitle->getPrefixedText(),
			'isSourceTranslationPage' => false
		] );

		$content = WikiPage::factory( $target )->getContent( Revision::RAW );
		$this->assertNotNull( $content );
		$this->assertStringContainsString(
			$pageMessageContent,
			$content->getNativeData()
		);
	}

	/**
	 * @covers \MediaWiki\MassMessage\MassMessageJob::makeAPIRequest
	 */
	public function testPageMessageSendingFailToEdit() {
		$pageMessageContent = 'Test page message.';
		$pageMessageTitleStr = 'PageMessage';

		$target = $this->getTargetTitle( 'Project:Testing1234' );
		$pageMessageTitle = $this->createPage( $pageMessageTitleStr, $pageMessageContent );
		// Force read-only mode - this will make page editing fail and test that
		// job fails without creating exceptions
		$this->setMwGlobals( 'wgReadOnly', 'testing' );
		list( , $result ) = $this->simulateJob( $target, [
			'page-message' => $pageMessageTitleStr,
			'pageMessageTitle' => $pageMessageTitle->getPrefixedText(),
			'isSourceTranslationPage' => false
		] );
		$this->assertFalse( $result );
	}

	/**
	 * @covers \MediaWiki\MassMessage\MassMessageJob::sendMessage
	 */
	public function testTranslatablePageMessageSending() {
		$this->setMwGlobals( [
			'wgPageLanguageUseDB' => true,
		] );

		$pageMessageContent = 'Test page message - FR.';
		$pageMessageTitleStr = 'PageMessage';

		// Create target user talk page and change the page language.
		$target = $this->getTargetTitle( 'Project:Testing1234' );
		$this->createPageByTitle( $target, '' );
		$requestContext = new RequestContext();
		$requestContext->setLanguage( 'en' );
		$requestContext->setUser( $this->getTestSysop()->getUser() );
		$status = SpecialPageLanguage::changePageLanguage(
			$requestContext, $target, 'fr', 'testing'
		);

		if ( !$status->isOK() ) {
			throw new RuntimeException(
				"Unable to change page language. Error: " . $status->getMessage()
			);
		}

		// Create the message page with /fr suffix
		$this->createPage( $pageMessageTitleStr . '/fr', $pageMessageContent );

		$this->simulateJob( $target, [
			'page-message' => 'PageMessage',
			'pageMessageTitle' => Title::newFromText( $pageMessageTitleStr )->getPrefixedText(),
			'isSourceTranslationPage' => true,
			'originWiki' => WikiMap::getCurrentWikiId()
		] );

		$content = WikiPage::factory( $target )->getContent( Revision::RAW );
		$this->assertNotNull( $content );
		$this->assertStringContainsString(
			$pageMessageContent,
			$content->getNativeData()
		);
	}

}

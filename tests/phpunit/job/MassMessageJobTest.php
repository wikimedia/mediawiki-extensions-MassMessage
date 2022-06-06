<?php

namespace MediaWiki\MassMessage;

use ContentHandler;
use ExtensionRegistry;
use MediaWiki\MassMessage\Job\MassMessageJob;
use MediaWiki\MediaWikiServices;
use MediaWiki\Revision\RevisionRecord;
use MWCryptRand;
use RequestContext;
use RuntimeException;
use SpecialPageLanguage;
use TextContent;
use Title;
use User;
use WikiMap;

class MassMessageJobTest extends MassMessageTestCase {

	/**
	 * Runs a job to edit the given title
	 * @param Title $title
	 * @param array $additionalParams
	 * @return array
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
			$wikipage = $this->getServiceContainer()->getWikiPageFactory()->newFromTitle( $target );
			$wikipage->doDeleteArticleReal( 'reason', $this->getTestSysop()->getUser() );
		}

		return $target;
	}

	private function createPage( string $titleStr, string $pageContent ): Title {
		$title = Title::newFromText( $titleStr );
		$this->createPageByTitle( $title, $pageContent );
		return $title;
	}

	private function createPageByTitle(
		Title $title, string $pageContent, string $langCode = null
	) {
		$page = $this->getServiceContainer()->getWikiPageFactory()->newFromTitle( $title );
		$content = ContentHandler::makeContent( $pageContent, $title );

		$status = $page->doUserEditContent(
			$content,
			$this->getTestSysop()->getUser(),
			__METHOD__
		);

		if ( !$status->isOK() ) {
			throw new RuntimeException(
				'There was an error while creating the page message with title - ' .
				$title->getPrefixedText()
			);
		}

		if ( $langCode ) {
			$requestContext = new RequestContext();
			$requestContext->setLanguage( 'en' );
			$requestContext->setUser( $this->getTestSysop()->getUser() );
			$status = SpecialPageLanguage::changePageLanguage(
				$requestContext, $title, $langCode, 'testing'
			);

			if ( !$status->isOK() ) {
				throw new RuntimeException(
					"Unable to change page language. Error: " . $status->getMessage()
				);
			}
		}
	}

	/**
	 * @covers \MediaWiki\MassMessage\Job\MassMessageJob::sendMessage
	 * @covers \MediaWiki\MassMessage\Job\MassMessageJob::editPage
	 */
	public function testMessageSending() {
		$target = $this->getTargetTitle( 'Project:Testing1234' );
		list( $subj, ) = $this->simulateJob( $target );
		$target = Title::makeTitle( NS_PROJECT, 'Testing1234' );
		// Message was created
		/** @var TextContent $content */
		'@phan-var TextContent $content';
		$content = $this->getServiceContainer()->getWikiPageFactory()
			->newFromTitle( $target )->getContent( RevisionRecord::RAW );
		$text = $content->getText();
		$this->assertEquals(
			"== $subj ==\n\nThis is a message.\n<!-- Message sent by User:Admin@metawiki" .
			" using the list at http://meta.wikimedia.org/w/index.php?title=Spamlist&oldid=5 -->",
			$text
		);
	}

	/**
	 * @covers \MediaWiki\MassMessage\Job\MassMessageJob::addLQTThread
	 * @covers \MediaWiki\MassMessage\Job\MassMessageJob::sendMessage
	 * @covers \MediaWiki\MassMessage\MessageSender::addLQTThread
	 * @covers \MediaWiki\MassMessage\MessageSender::makeAPIRequest
	 */
	public function testLQTMessageSending() {
		$this->markTestSkipped( 'broken test, T217553' );

		$proj = MediaWikiServices::getInstance()->getContentLanguage()
			->getFormattedNsText( NS_PROJECT ); // Output changes based on wikiname

		if ( !ExtensionRegistry::getInstance()->isLoaded( 'Liquid Threads' ) ) {
			$this->markTestSkipped( "This test requires the LiquidThreads extension" );
		}
		$target = Title::makeTitle( NS_PROJECT, 'LQT test' );
		// $this->assertTrue( LqtDispatch::isLqtPage( $target ) );
		// Check that it worked
		list( $subject, ) = $this->simulateJob( $target );
		$this->assertTrue( Title::makeTitle( NS_LQT_THREAD, $proj . ':LQT test/' . $subject )->exists() );
	}

	/**
	 * @covers \MediaWiki\MassMessage\Job\MassMessageJob::isOptedOut
	 */
	public function testOptOut() {
		$fakejob = new MassMessageJob( Title::newMainPage(), [] );
		$target = Title::makeTitle( NS_PROJECT, 'Opt out test page' );
		self::updatePage( $target, '[[Category:Opted-out of message delivery]]' );
		$this->assertTrue( $fakejob->isOptedOut( $target ) );
		$this->assertFalse( $fakejob->isOptedOut(
			Title::makeTitle( NS_PROJECT, 'Some random page' )
		) );
		$this->simulateJob( $target ); // Try posting a message to this page
		/** @var TextContent $content */
		'@phan-var TextContent $content';
		$content = $this->getServiceContainer()->getWikiPageFactory()
			->newFromTitle( $target )->getContent( RevisionRecord::RAW );
		$text = $content->getText();
		// Nothing should be updated.
		$this->assertEquals( '[[Category:Opted-out of message delivery]]', $text );
	}

	/**
	 * @covers \MediaWiki\MassMessage\Job\MassMessageJob::sendMessage
	 * @covers \MediaWiki\MassMessage\MessageSender::editPage
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

		$content = $this->getServiceContainer()->getWikiPageFactory()
			->newFromTitle( $target )->getContent( RevisionRecord::RAW );
		$this->assertNotNull( $content );
		/** @var TextContent $content */
		'@phan-var TextContent $content';
		$this->assertStringContainsString(
			$pageMessageContent,
			$content->getText()
		);
	}

	/**
	 * @covers \MediaWiki\MassMessage\Job\MassMessageJob::sendMessage
	 * @covers \MediaWiki\MassMessage\MessageSender::editPage
	 */
	public function testPageMessageSendingFailToEdit() {
		$pageMessageContent = 'Test page message.';
		$pageMessageTitleStr = 'PageMessage';

		$target = $this->getTargetTitle( 'Project:Testing1234' );
		$pageMessageTitle = $this->createPage( $pageMessageTitleStr, $pageMessageContent );
		// Set a hook handler to make page editing fail and test that
		// job fails without creating exceptions
		$this->setTemporaryHook( 'EditFilter', static function ( $editor, $text, $section, &$error ): bool {
			$error = 'Failing for testPageMessageSendingFailToEdit';

			return false;
		} );
		list( , $result ) = $this->simulateJob( $target, [
			'page-message' => $pageMessageTitleStr,
			'pageMessageTitle' => $pageMessageTitle->getPrefixedText(),
			'isSourceTranslationPage' => false
		] );
		$this->assertFalse( $result );
	}

	/**
	 * @covers \MediaWiki\MassMessage\Job\MassMessageJob::sendMessage
	 * @covers \MediaWiki\MassMessage\MessageSender::editPage
	 */
	public function testTranslatablePageMessageSending() {
		$this->setMwGlobals( [
			'wgPageLanguageUseDB' => true,
		] );

		$pageMessageContent = 'Test page message - FR.';
		$pageMessageTitleStr = 'PageMessage';

		// Create target user talk page and change the page language.
		$target = $this->getTargetTitle( 'Project:Testing1234' );
		$this->createPageByTitle( $target, '', 'fr' );

		// Create the message page with /fr suffix
		$this->createPage( $pageMessageTitleStr . '/fr', $pageMessageContent );

		$this->simulateJob( $target, [
			'page-message' => 'PageMessage',
			'pageMessageTitle' => Title::newFromText( $pageMessageTitleStr )->getPrefixedText(),
			'isSourceTranslationPage' => true,
			'originWiki' => WikiMap::getCurrentWikiId()
		] );

		$content = $this->getServiceContainer()->getWikiPageFactory()
			->newFromTitle( $target )->getContent( RevisionRecord::RAW );
		$this->assertNotNull( $content );
		/** @var TextContent $content */
		'@phan-var TextContent $content';
		$this->assertStringContainsString(
			$pageMessageContent,
			$content->getText()
		);
	}

	/**
	 * @covers \MediaWiki\MassMessage\Job\MassMessageJob::sendMessage
	 * @covers \MediaWiki\MassMessage\MessageSender::editPage
	 */
	public function testTranslatableFallback() {
		$this->setMwGlobals( [
			'wgPageLanguageUseDB' => true,
		] );

		$pageMessageContent = 'Test page message - PT.';
		$pageMessageTitleStr = 'PageMessage - PT';

		// Create target user talk page and change the page language.
		$target = $this->getTargetTitle( 'Project:Testing1234' );
		$this->createPageByTitle( $target, '', 'pt-br' );

		// Create the message page with /pt suffix
		$this->createPage( $pageMessageTitleStr . '/pt', $pageMessageContent );

		$this->simulateJob( $target, [
			'page-message' => 'PageMessage',
			'pageMessageTitle' => Title::newFromText( $pageMessageTitleStr )->getPrefixedText(),
			'isSourceTranslationPage' => true,
			'originWiki' => WikiMap::getCurrentWikiId()
		] );

		$content = $this->getServiceContainer()->getWikiPageFactory()
			->newFromTitle( $target )->getContent( RevisionRecord::RAW );
		$this->assertNotNull( $content );
		/** @var TextContent $content */
		'@phan-var TextContent $content';
		$this->assertStringContainsString(
			$pageMessageContent,
			$content->getText()
		);
	}

	/**
	 * @covers \MediaWiki\MassMessage\Job\MassMessageJob::sendMessage
	 * @covers \MediaWiki\MassMessage\MessageSender::editPage
	 */
	public function testTranslatableFallbackSource() {
		$this->setMwGlobals( [
			'wgPageLanguageUseDB' => true,
		] );

		$pageMessageContent = 'Test page message - EN.';
		$pageMessageTitleStr = 'PageMessage - EN';

		// Create target user talk page and change the page language.
		$target = $this->getTargetTitle( 'Project:Testing1234' );
		$this->createPageByTitle( $target, '', 'pt-br' );

		// Create the message page with /en suffix
		$this->createPage( $pageMessageTitleStr . '/en', $pageMessageContent );

		$this->simulateJob( $target, [
			'page-message' => 'PageMessage',
			'pageMessageTitle' => Title::newFromText( $pageMessageTitleStr )->getPrefixedText(),
			'isSourceTranslationPage' => true,
			'translationPageSourceLanguage' => 'en',
			'originWiki' => WikiMap::getCurrentWikiId()
		] );

		$content = $this->getServiceContainer()->getWikiPageFactory()
			->newFromTitle( $target )->getContent( RevisionRecord::RAW );
		$this->assertNotNull( $content );
		/** @var TextContent $content */
		'@phan-var TextContent $content';
		$this->assertStringContainsString(
			$pageMessageContent,
			$content->getText()
		);
	}

}

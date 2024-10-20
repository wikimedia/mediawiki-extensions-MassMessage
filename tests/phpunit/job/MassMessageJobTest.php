<?php

namespace MediaWiki\MassMessage;

use MediaWiki\Content\ContentHandler;
use MediaWiki\Content\TextContent;
use MediaWiki\Context\RequestContext;
use MediaWiki\Json\FormatJson;
use MediaWiki\MainConfigNames;
use MediaWiki\MassMessage\Job\MassMessageJob;
use MediaWiki\MediaWikiServices;
use MediaWiki\Revision\RevisionRecord;
use MediaWiki\Specials\SpecialPageLanguage;
use MediaWiki\Title\Title;
use MediaWiki\WikiMap\WikiMap;
use MWCryptRand;
use RuntimeException;

/**
 * @group Database
 */
class MassMessageJobTest extends MassMessageTestCase {

	/**
	 * Runs a job to edit the given title
	 * @param Title $title
	 * @param array $additionalParams
	 * @param string|null $subject Subject for the message; randomly generated if unspecified
	 * @return array
	 */
	private function simulateJob( Title $title, array $additionalParams = [], $subject = null ): array {
		if ( $subject === null ) {
			$subject = md5( MWCryptRand::generateHex( 15 ) );
		}
		$params = [
			'subject' => $subject,
			'message' => 'This is a message.',
		];
		$params = array_merge( $params, $additionalParams );
		$params['comment'] = [
			'Admin',
			'metawiki',
			'http://meta.wikimedia.org/w/index.php?title=Spamlist&oldid=5'
		];
		$job = new MassMessageJob( $title, $params );
		return [ $subject, $job->run() ];
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

		$status = $this->editPage( $page, $content );

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
		$target = $this->getNonexistingTestPage( 'Project:Testing1234' )->getTitle();
		[ $subj, ] = $this->simulateJob( $target );
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
	 * @covers \MediaWiki\MassMessage\Job\MassMessageJob::sendMessage
	 * @covers \MediaWiki\MassMessage\DedupeHelper::hasRecentlyDeliveredDuplicate
	 * @covers \MediaWiki\MassMessage\MessageSender::editPage
	 */
	public function testDedupe() {
		$services = MediaWikiServices::getInstance();
		$dbr = $services->getDBLoadBalancer()->getConnection( DB_REPLICA );

		$target = $this->getNonexistingTestPage( 'Project:DedupeTest' )->getTitle();
		[ $subject, ] = $this->simulateJob( $target );
		// Send the same message again
		$this->simulateJob( $target, [], $subject );
		$target = Title::makeTitle( NS_PROJECT, 'DedupeTest' );
		$content = $this->getServiceContainer()->getWikiPageFactory()
			->newFromTitle( $target )->getContent( RevisionRecord::RAW );
		$text = $content->getText();
		// There should only be one copy of the message
		$this->assertEquals(
			"== $subject ==\n\nThis is a message.\n<!-- Message sent by User:Admin@metawiki" .
			" using the list at http://meta.wikimedia.org/w/index.php?title=Spamlist&oldid=5 -->",
			$text
		);

		$dedupe_hash = DedupeHelper::getDedupeHash( $subject, 'This is a message.', null, null );
		$revision = $services->getRevisionStore()->getRevisionByTitle( $target );
		$change_tags = $services->getChangeTagsStore()->getTagsWithData( $dbr, null, $revision->getId() );
		$this->assertEquals(
			[ 'massmessage-delivery' => FormatJson::encode( [ 'dedupe_hash' => $dedupe_hash ] ) ],
			$change_tags,
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
			// Output changes based on wikiname
			->getFormattedNsText( NS_PROJECT );

		$this->markTestSkippedIfExtensionNotLoaded( 'Liquid Threads' );
		$target = Title::makeTitle( NS_PROJECT, 'LQT test' );
		// $this->assertTrue( LqtDispatch::isLqtPage( $target ) );
		// Check that it worked
		[ $subject, ] = $this->simulateJob( $target );
		$this->assertTrue( Title::makeTitle( NS_LQT_THREAD, $proj . ':LQT test/' . $subject )->exists() );
	}

	/**
	 * @covers \MediaWiki\MassMessage\Job\MassMessageJob::isOptedOut
	 */
	public function testOptOut() {
		$fakejob = new MassMessageJob( Title::newMainPage(), [] );
		$target = Title::makeTitle( NS_PROJECT, 'Opt out test page' );
		$this->updatePage( $target, '[[Category:Opted-out of message delivery]]' );
		$this->assertTrue( $fakejob->isOptedOut( $target ) );
		$this->assertFalse( $fakejob->isOptedOut(
			Title::makeTitle( NS_PROJECT, 'Some random page' )
		) );
		// Try posting a message to this page
		$this->simulateJob( $target );
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

		$target = $this->getNonexistingTestPage( 'Project:Testing1234' )->getTitle();
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
	 * @covers \MediaWiki\MassMessage\DedupeHelper::hasRecentlyDeliveredDuplicate
	 * @covers \MediaWiki\MassMessage\MessageSender::editPage
	 */
	public function testDedupePageMessageSending() {
		$pageMessageContent = 'Test page message.';
		$pageMessageTitleStr = 'PageMessage';

		$target = $this->getNonexistingTestPage( 'Project:DedupePageMessageTest' )->getTitle();
		$pageMessageTitle = $this->createPage( $pageMessageTitleStr, $pageMessageContent );

		[ $subject, ] = $this->simulateJob( $target, [
			'page-message' => $pageMessageTitleStr,
			'pageMessageTitle' => $pageMessageTitle->getPrefixedText(),
			'isSourceTranslationPage' => false
		] );
		// Send the same message again
		$this->simulateJob(
			$target,
			[
				'page-message' => $pageMessageTitleStr,
				'pageMessageTitle' => $pageMessageTitle->getPrefixedText(),
				'isSourceTranslationPage' => false
			],
			$subject,
		);

		$target = Title::makeTitle( NS_PROJECT, 'DedupePageMessageTest' );
		$content = $this->getServiceContainer()->getWikiPageFactory()
			->newFromTitle( $target )->getContent( RevisionRecord::RAW );
		$text = $content->getText();
		// There should only be one copy of the message
		$this->assertEquals(
			"== $subject ==\n\nTest page message.\n\nThis is a message.\n<!-- Message sent by User:Admin@metawiki" .
			" using the list at http://meta.wikimedia.org/w/index.php?title=Spamlist&oldid=5 -->",
			$text
		);
	}

	/**
	 * @covers \MediaWiki\MassMessage\Job\MassMessageJob::sendMessage
	 * @covers \MediaWiki\MassMessage\MessageSender::editPage
	 */
	public function testPageMessageSendingFailToEdit() {
		$pageMessageContent = 'Test page message.';
		$pageMessageTitleStr = 'PageMessage';

		$target = $this->getNonexistingTestPage( 'Project:Testing1234' )->getTitle();
		$pageMessageTitle = $this->createPage( $pageMessageTitleStr, $pageMessageContent );
		// Set a hook handler to make page editing fail and test that
		// job fails without creating exceptions
		$this->setTemporaryHook( 'EditFilter', static function ( $editor, $text, $section, &$error ): bool {
			$error = 'Failing for testPageMessageSendingFailToEdit';

			return false;
		} );
		[ , $result ] = $this->simulateJob( $target, [
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
		$this->overrideConfigValue( MainConfigNames::PageLanguageUseDB, true );

		$pageMessageContent = 'Test page message - FR.';
		$pageMessageTitleStr = 'PageMessage';

		// Create target user talk page and change the page language.
		$target = $this->getNonexistingTestPage( 'Project:Testing1234' )->getTitle();
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
		$this->overrideConfigValue( MainConfigNames::PageLanguageUseDB, true );

		$pageMessageContent = 'Test page message - PT.';
		$pageMessageTitleStr = 'PageMessage - PT';

		// Create target user talk page and change the page language.
		$target = $this->getNonexistingTestPage( 'Project:Testing1234' )->getTitle();
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
		$this->overrideConfigValue( MainConfigNames::PageLanguageUseDB, true );

		$pageMessageContent = 'Test page message - EN.';
		$pageMessageTitleStr = 'PageMessage - EN';

		// Create target user talk page and change the page language.
		$target = $this->getNonexistingTestPage( 'Project:Testing1234' )->getTitle();
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

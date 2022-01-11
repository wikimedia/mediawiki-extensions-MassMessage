<?php
declare( strict_types = 1 );

namespace MediaWiki\MassMessage\MessageContentFetcher;

use MediaWiki\MassMessage\LanguageAwareText;
use MediaWiki\MassMessage\Stub\RevisionStoreStubFactory;
use MediaWiki\MassMessage\Stub\TitleStubFactory;
use MediaWikiIntegrationTestCase;

class LocalMessageContentFetcherTest extends MediaWikiIntegrationTestCase {
	/**
	 * @covers \MediaWiki\MassMessage\MessageContentFetcher\LocalMessageContentFetcher::getContent
	 */
	public function testGetContentTitleNotFound() {
		$revisionStoreStub = ( new RevisionStoreStubFactory() )->getWithoutRevisionRecord();
		$titleStub = ( new TitleStubFactory() )->getNonExistingTitle( 'not-found', 'en', 'ltr' );

		$localContentFetcher = new LocalMessageContentFetcher( $revisionStoreStub );
		$status = $localContentFetcher->getContent( $titleStub );

		$this->assertStringContainsString(
			'"massmessage-page-message-not-found"', json_encode( $status->getErrors() )
		);
	}

	/**
	 * @covers \MediaWiki\MassMessage\MessageContentFetcher\LocalMessageContentFetcher::getContent
	 */
	public function testGetContentWithoutRevisionRecord() {
		$revisionStoreStub = ( new RevisionStoreStubFactory() )->getWithoutRevisionRecord();
		$titleStub = ( new TitleStubFactory() )->getExistingTitle( 'valid', 'en', 'ltr' );

		$localContentFetcher = new LocalMessageContentFetcher( $revisionStoreStub );
		$status = $localContentFetcher->getContent( $titleStub );

		$this->assertStringContainsString(
			'"massmessage-page-message-no-revision"', json_encode( $status->getErrors() )
		);
	}

	/**
	 * @covers \MediaWiki\MassMessage\MessageContentFetcher\LocalMessageContentFetcher::getContent
	 */
	public function testGetContent() {
		$contentText = 'This is the text!';
		$contentLanguage = 'he';
		$contentDir = 'rtl';

		$revisionStoreStub = ( new RevisionStoreStubFactory() )->getWithText( $contentText );
		$localContentFetcher = new LocalMessageContentFetcher( $revisionStoreStub );

		$titleStub = ( new TitleStubFactory() )->getExistingTitle( 'valid', $contentLanguage, $contentDir );
		$status = $localContentFetcher->getContent( $titleStub );
		$this->assertTrue( $status->isOK() );

		/** @var LanguageAwareText */
		$languageAwareText = $status->getValue();
		$this->assertEquals( $contentText, $languageAwareText->getWikitext() );
		$this->assertEquals( $contentLanguage, $languageAwareText->getLanguageCode() );
		$this->assertEquals( $contentDir, $languageAwareText->getLanguageDirection() );
	}
}

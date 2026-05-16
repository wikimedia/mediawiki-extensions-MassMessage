<?php
declare( strict_types = 1 );

namespace MediaWiki\MassMessage\MessageContentFetcher;

use MediaWiki\Content\TextContent;
use MediaWiki\Language\Language;
use MediaWiki\MassMessage\LanguageAwareText;
use MediaWiki\Revision\RevisionRecord;
use MediaWiki\Revision\RevisionStore;
use MediaWiki\Title\Title;
use MediaWikiIntegrationTestCase;

class LocalMessageContentFetcherTest extends MediaWikiIntegrationTestCase {
	/**
	 * @covers \MediaWiki\MassMessage\MessageContentFetcher\LocalMessageContentFetcher::getContent
	 */
	public function testGetContentTitleNotFound() {
		$revisionStoreStub = $this->getRevisionStoreWithoutRevisionRecord();
		$titleStub = $this->getNonExistingTitle( 'not-found', 'en', 'ltr' );

		$localContentFetcher = new LocalMessageContentFetcher( $revisionStoreStub );
		$status = $localContentFetcher->getContent( $titleStub );

		$this->assertStringContainsString(
			'"massmessage-page-message-not-found"', json_encode( $status->getErrors() )
		);
	}

	private function getRevisionStoreWithText( string $textContent ): RevisionStore {
		$revisionRecordStub = $this->createStub( RevisionRecord::class );
		$revisionRecordStub->method( 'getContent' )
			->willReturn( new TextContent( $textContent ) );

		$revisionStoreStub = $this->createStub( RevisionStore::class );
		$revisionStoreStub->method( 'getRevisionByTitle' )
			->willReturn( $revisionRecordStub );

		return $revisionStoreStub;
	}

	private function getRevisionStoreWithoutRevisionRecord(): RevisionStore {
		$revisionRecordStub = $this->createStub( RevisionStore::class );
		$revisionRecordStub->method( 'getRevisionByTitle' )
			->willReturn( null );

		return $revisionRecordStub;
	}

	private function getExistingTitle( string $titleStr, string $languageCode, string $languageDir ): Title {
		return $this->getTitleStub( $titleStr, true, $languageCode, $languageDir );
	}

	private function getNonExistingTitle( string $titleStr, string $languageCode, string $languageDir ): Title {
		return $this->getTitleStub( $titleStr, false, $languageCode, $languageDir );
	}

	private function getTitleStub( string $titleStr, bool $exists, string $languageCode, string $languageDir ): Title {
		$titleStub = $this->createStub( Title::class );
		$titleStub->method( 'exists' )
			->willReturn( $exists );

		$titleStub->method( 'getPrefixedText' )
			->willReturn( $titleStr );

		$languageStub = $this->createStub( Language::class );
		$languageStub->method( 'getCode' )
			->willReturn( $languageCode );

		$languageStub->method( 'getDir' )
			->willReturn( $languageDir );

		$titleStub->method( 'getPageLanguage' )
			->willReturn( $languageStub );

		return $titleStub;
	}

	/**
	 * @covers \MediaWiki\MassMessage\MessageContentFetcher\LocalMessageContentFetcher::getContent
	 */
	public function testGetContentWithoutRevisionRecord() {
		$revisionStoreStub = $this->getRevisionStoreWithoutRevisionRecord();
		$titleStub = $this->getExistingTitle( 'valid', 'en', 'ltr' );

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

		$revisionStoreStub = $this->getRevisionStoreWithText( $contentText );
		$localContentFetcher = new LocalMessageContentFetcher( $revisionStoreStub );

		$titleStub = $this->getExistingTitle( 'valid', $contentLanguage, $contentDir );
		$status = $localContentFetcher->getContent( $titleStub );
		$this->assertStatusOK( $status );

		/** @var LanguageAwareText */
		$languageAwareText = $status->getValue();
		$this->assertEquals( $contentText, $languageAwareText->getWikitext() );
		$this->assertEquals( $contentLanguage, $languageAwareText->getLanguageCode() );
		$this->assertEquals( $contentDir, $languageAwareText->getLanguageDirection() );
	}
}

<?php
declare( strict_types = 1 );

namespace MediaWiki\MassMessage\PageMessage;

use MediaWiki\Languages\LanguageFallback;
use MediaWiki\Languages\LanguageNameUtils;
use MediaWiki\MassMessage\LanguageAwareText;
use MediaWiki\MassMessage\MessageContentFetcher\LabeledSectionContentFetcher;
use MediaWiki\MassMessage\MessageContentFetcher\LocalMessageContentFetcher;
use MediaWiki\MassMessage\MessageContentFetcher\RemoteMessageContentFetcher;
use MediaWiki\Status\Status;
use MediaWiki\Title\Title;
use MediaWiki\WikiMap\WikiMap;
use MediaWikiIntegrationTestCase;

/** @coversDefaultClass \MediaWiki\MassMessage\PageMessage\PageMessageBuilder */
class PageMessageBuilderTest extends MediaWikiIntegrationTestCase {
	/**
	 * @covers \MediaWiki\MassMessage\PageMessage\PageMessageBuilder::getContent
	 * @dataProvider provideGetContent
	 */
	public function testGetContent(
		string $text,
		string $sectionSubject,
		string $sectionMessage,
		?string $expectedSubject,
		?string $expectedMessage,
		string $expectedMsgKey = null
	) {
		$localMessageContentFetcherMock = $this->createMock( LocalMessageContentFetcher::class );
		$localMessageContentFetcherMock
			->expects( $this->once() )
			->method( 'getContent' )
			->willReturn( Status::newGood( new LanguageAwareText( $text, 'en', 'ltr' ) ) );

		$pageMessageBuilder = new PageMessageBuilder(
			$localMessageContentFetcherMock,
			new LabeledSectionContentFetcher(),
			$this->createStub( RemoteMessageContentFetcher::class ),
			$this->createStub( LanguageNameUtils::class ),
			$this->createStub( LanguageFallback::class ),
			WikiMap::getCurrentWikiId()
		);

		$result = $pageMessageBuilder
			->getContent( 'hello', $sectionMessage, $sectionSubject, WikiMap::getCurrentWikiId() );

		if ( $expectedMessage === null ) {
			$this->assertNull( $result->getPageMessage() );
		} else {
			$this->assertEquals( $expectedMessage, $result->getPageMessage()->getWikitext() );
		}

		if ( $expectedSubject === null ) {
			$this->assertNull( $result->getPageSubject() );
		} else {
			$this->assertEquals( $expectedSubject, $result->getPageSubject()->getWikitext() );
		}

		if ( $expectedMsgKey ) {
			$this->assertStringContainsString( $expectedMsgKey, json_encode( $result->getStatus()->getErrors() ) );
		}
	}

	public static function provideGetContent() {
		yield "subject and message sections" => [
			'<section begin="sub" />Hello World<section end="sub" />' .
			'<section begin="message" />This is the message<section end="message" /> Some more text',
			'"sub"',
			'"message"',
			'Hello World',
			'<section begin="message" />This is the message<section end="message" />'
		];

		yield "subject section absent" => [
			'<section begin="sub" />Hello World<section end="sub" />' .
			'<section begin="message" />This is the message<section end="message" /> Some more text',
			'"sub1234"',
			'"message"',
			null,
			'<section begin="message" />This is the message<section end="message" />',
			'"massmessage-page-section-invalid"'
		];

		yield "message section absent" => [
			'<section begin="sub" />Hello World<section end="sub" />' .
			'<section begin="message" />This is the message<section end="message" /> Some more text',
			'"sub"',
			'"message123"',
			'Hello World',
			null,
			'"massmessage-page-section-invalid"'
		];

		yield "no selected section" => [
			'hello world how are you?',
			'',
			'',
			null,
			'hello world how are you?'
		];
	}

	/**
	 * @covers \MediaWiki\MassMessage\PageMessage\PageMessageBuilder::getContentWithFallback
	 * @dataProvider provideGetContentWithFallback
	 */
	public function testGetContentWithFallback(
		$callback,
		array $fallbackChain,
		string $targetLanguageCode,
		string $sourceLanguageCode,
		string $expectedContent,
		string $expectedLanguageCode
	) {
		$localMessageContentFetcherStub = $this->createStub( LocalMessageContentFetcher::class );
		$localMessageContentFetcherStub
			->method( 'getContent' )
			->willReturnCallback( $callback );

		$languageNameUtilsStub = $this->createStub( LanguageNameUtils::class );
		$languageNameUtilsStub
			->method( 'isKnownLanguageTag' )
			->willReturn( true );

		$languageFallbackStub = $this->createStub( LanguageFallback::class );
		$languageFallbackStub
			->method( 'getAll' )
			->willReturn( $fallbackChain );

		$pageMessageBuilder = new PageMessageBuilder(
			$localMessageContentFetcherStub,
			new LabeledSectionContentFetcher(),
			$this->createStub( RemoteMessageContentFetcher::class ),
			$languageNameUtilsStub,
			$languageFallbackStub,
			WikiMap::getCurrentWikiId()
		);

		$result = $pageMessageBuilder->getContentWithFallback(
			'helloworld',
			$targetLanguageCode,
			$sourceLanguageCode,
			null,
			null,
			WikiMap::getCurrentWikiId()
		);

		$this->assertTrue( $result->isOK() );

		/** @var LanguageAwareText */
		$pageMessage = $result->getPageMessage();
		$this->assertEquals( $expectedContent, $pageMessage->getWikitext() );
		$this->assertEquals( $expectedLanguageCode, $pageMessage->getLanguageCode() );
	}

	public static function provideGetContentWithFallback() {
		$content = 'hello world';
		$expectedLanguageCode = 'pt';

		yield "fallback language is not used if target language is present" => [
			self::getContentProviderCallback( $content, $expectedLanguageCode, 'ltr' ),
			[ 'pt-br' ],
			$expectedLanguageCode,
			'en',
			$content,
			$expectedLanguageCode
		];

		$expectedLanguageCode = 'pt-br';
		yield "fallback language is used if target language is present" => [
			self::getContentProviderCallback( $content, $expectedLanguageCode, 'ltr' ),
			[ $expectedLanguageCode ],
			'pt',
			'en',
			$content,
			$expectedLanguageCode
		];

		$expectedLanguageCode = 'en';
		yield "source language is used if target or fallbacks are not present" => [
			self::getContentProviderCallback( $content, $expectedLanguageCode, 'ltr' ),
			[ 'pt-br' ],
			'pt',
			$expectedLanguageCode,
			$content,
			$expectedLanguageCode
		];
	}

	private static function getContentProviderCallback(
		string $content, string $languageCode, string $languageDirection
	) {
		return static function ( Title $title ) use ( $content, $languageCode, $languageDirection ) {
			if ( str_ends_with( $title->getPrefixedText(), "/$languageCode" ) ) {
				return Status::newGood(
					new LanguageAwareText(
						$content,
						$languageCode,
						$languageDirection
					)
				);
			}

			return Status::newFatal(
				'massmessage-page-message-not-found',
				'hello-world',
				WikiMap::getCurrentWikiId()
			);
		};
	}
}

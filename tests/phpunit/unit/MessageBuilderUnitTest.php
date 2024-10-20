<?php
declare( strict_types = 1 );

namespace MediaWiki\MassMessage;

use MediaWiki\Language\Language;
use MediaWikiUnitTestCase;

class MessageBuilderUnitTest extends MediaWikiUnitTestCase {
	/**
	 * @covers \MediaWiki\MassMessage\MessageBuilder::stripTildes
	 * @dataProvider provideStripTildes
	 */
	public function testStripTildes(
		string $message,
		string $expected
	) {
		$messageBuilder = new MessageBuilder();
		$strippedMessage = $messageBuilder->stripTildes( $message );

		$this->assertEquals( $expected, $strippedMessage );
	}

	public static function provideStripTildes() {
		yield 'removes tildes if message has 4 tildes at end' => [
			'hello ~~~~',
			'hello '
		];

		yield 'does not remove tildes if message has more than 4 tildes at end' => [
			'hello ~~~~~',
			'hello ~~~~~'
		];

		yield 'does not remove tildes if message has less than 4 tildes at end' => [
			'hello ~~~',
			'hello ~~~'
		];
	}

	/**
	 * @covers \MediaWiki\MassMessage\MessageBuilder::buildMessage
	 * @covers \MediaWiki\MassMessage\MessageBuilder::wrapBasedOnLanguage
	 * @dataProvider provideBuildMessage
	 */
	public function testBuildMessage(
		string $customMessageText,
		?LanguageAwareText $pageContent,
		?string $mockTargetLanguageCode,
		?string $mockTargetLanguageDir,
		?string $expectedMessage,
		?array $expectedMessageContents
	) {
		$targetLanguage = $mockTargetLanguageCode === null
			? null : $this->getLanguageStub( $mockTargetLanguageCode, $mockTargetLanguageDir );
		$messageBuilder = new MessageBuilder();
		$message = $messageBuilder->buildMessage(
			$customMessageText,
			$pageContent,
			$targetLanguage,
			// Forced to use commentParams as [] as it uses the MessageCache that requires MediaWikiServices
			[]
		);

		if ( $expectedMessage ) {
			$this->assertEquals( $expectedMessage, $message );
		}

		// Check if all the strings are present in the message
		if ( $expectedMessageContents ) {
			foreach ( $expectedMessageContents as $needle ) {
				$this->assertStringContainsString( $needle, $message );
			}
		}
	}

	public static function provideBuildMessage() {
		yield 'message and page content are both present' => [
			'hello world',
			new LanguageAwareText( 'how are you', 'en', 'ltr' ),
			'en', 'ltr',
			null,
			[ 'hello world', 'how are you' ]
		];

		yield 'no language wrapping if message and target page language are same' => [
			'',
			new LanguageAwareText( 'how are you', 'fr', 'ltr' ),
			'fr', 'ltr',
			'how are you',
			null
		];

		yield 'language wrapping if message and target page language are different' => [
			'',
			new LanguageAwareText( 'how are you', 'he', 'rtl' ),
			'fr', 'ltr',
			null,
			[ 'dir="rtl"', 'lang="he"' ]
		];

		yield 'language wrapping is applied if target language is null' => [
			'',
			new LanguageAwareText( 'how are you', 'en', 'ltr' ),
			null, null,
			null,
			[ 'dir="ltr"', 'lang="en"' ]
		];
	}

	/**
	 * @covers \MediaWiki\MassMessage\MessageBuilder::buildSubject
	 * @covers \MediaWiki\MassMessage\MessageBuilder::wrapBasedOnLanguage
	 * @covers \MediaWiki\MassMessage\MessageBuilder::needsWrapping
	 * @covers \MediaWiki\MassMessage\MessageBuilder::wrapContentWithLanguageAttributes
	 * @dataProvider provideBuildSubject
	 */
	public function testBuildSubject(
		string $customSubject,
		?LanguageAwareText $pageSubject,
		?string $mockTargetPageLanguageCode,
		?string $mockTargetPageLanguageDir,
		?string $expected,
		?array $expectedContents
	) {
		$targetPageLanguage = $this->getLanguageStub( $mockTargetPageLanguageCode, $mockTargetPageLanguageDir );
		$messageBuilder = new MessageBuilder();
		$subject = $messageBuilder->buildSubject(
			$customSubject,
			$pageSubject,
			$targetPageLanguage
		);

		if ( $expected ) {
			$this->assertEquals( $expected, $subject );
		}

		if ( $expectedContents ) {
			foreach ( $expectedContents as $needle ) {
				$this->assertStringContainsString( $needle, $subject );
			}
		}
	}

	public static function provideBuildSubject() {
		yield 'custom subject is used if page subject is absent' => [
			'hello world',
			null,
			'en', 'ltr',
			'hello world',
			null
		];

		yield 'custom subject is ignored if page subject is present' => [
			'hello world',
			new LanguageAwareText( 'how are you', 'en', 'ltr' ),
			'en', 'ltr',
			'how are you',
			null
		];

		yield 'no language wrapping if target and page language are same' => [
			'',
			new LanguageAwareText( 'how are you', 'he', 'rtl' ),
			'he', 'rtl',
			'how are you',
			null
		];

		yield 'language wrapping if message and target page language are same' => [
			'',
			new LanguageAwareText( 'how are you', 'he', 'rtl' ),
			'fr', 'ltr',
			null,
			[ 'dir="rtl"', 'lang="he"', '</span>' ]
		];

		yield 'html tags are removed' => [
			'',
			new LanguageAwareText( 'hello <span>world</span>', 'en', 'ltr' ),
			'en', 'rtl',
			'hello world',
			null
		];

		yield 'language tags are still used if message has html tags' => [
			'',
			new LanguageAwareText( 'hello <span>world</span>', 'en', 'ltr' ),
			'he', 'rtl',
			null,
			[ 'dir="ltr"', 'lang="en"', '</span>' ]
		];
	}

	/**
	 * @covers \MediaWiki\MassMessage\MessageBuilder::buildPlaintextSubject
	 * @covers \MediaWiki\MassMessage\MessageBuilder::sanitizeSubject
	 * @dataProvider provideBuildPlaintextSubject
	 */
	public function testBuildPlaintextSubject(
		string $customSubject,
		?LanguageAwareText $pageSubject,
		string $expected
	) {
		$messageBuilder = new MessageBuilder();
		$subject = $messageBuilder->buildPlaintextSubject( $customSubject, $pageSubject );

		$this->assertEquals( $expected, $subject );
	}

	public static function provideBuildPlaintextSubject() {
		yield 'remove newlines and tags from page subject' => [
			'',
			new LanguageAwareText( "Hello World\n <br><hr>How are you? ", 'en', 'ltr' ),
			"Hello World How are you?"
		];

		yield 'remove only trailing white spaces' => [
			'',
			new LanguageAwareText( " Hello World \n", 'en', 'ltr' ),
			" Hello World"
		];

		yield 'remove newlines and tags from custom message' => [
			"Hello World\n <br><hr>How are you? ",
			null,
			"Hello World How are you?"
		];

		yield 'strips html for language wrapping' => [
			"<div lang='en' dir='ltr'>Hello World</div> ",
			null,
			"Hello World"
		];
	}

	private function getLanguageStub( string $languageCode, string $languageDir ) {
		$languageStub = $this->createStub( Language::class );
		$languageStub->method( 'getCode' )
			->willReturn( $languageCode );

		$languageStub->method( 'getDir' )
			->willReturn( $languageDir );

		return $languageStub;
	}
}

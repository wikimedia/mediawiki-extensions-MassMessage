<?php

namespace MediaWiki\MassMessage;

use Title;
use WikiPage;
use ContentHandler;

/**
 * Tests for MassMessage List Content related to target processing
 */
class ListContentSpamlistLookupTest extends MassMessageTestCase {
	/**
	 * @covers \MediaWiki\MassMessage\SpamlistLookup::getTargets
	 * @covers \MediaWiki\MassMessage\ListContentSpamlistLookup::fetchTargets
	 */
	public function testGetTargets() {
		$text = '{"description":"","targets":['
			. '{"title":"A"},'
			. '{"title":"B","site":"en.wikipedia.org"},'
			. '{"title":"C","site":"invalid.org"}'
			. ']}';
		$content = ContentHandler::makeContent( $text, null, 'MassMessageListContent' );
		$title = Title::newFromText( 'MassMessageListContent_spamlist' );
		$page = WikiPage::factory( $title );
		$page->doEditContent( $content, 'summary' );
		$targets = SpamlistLookup::getTargets( $title );
		$this->assertEquals( 2, count( $targets ) );
		$this->assertEquals( 'A', $targets[0]['title'] );
		$this->assertEquals( wfWikiId(), $targets[0]['wiki'] );
		$this->assertEquals( 'B', $targets[1]['title'] );
		$this->assertEquals( 'enwiki', $targets[1]['wiki'] );
		$this->assertEquals( 'en.wikipedia.org', $targets[1]['site'] );
	}

	/**
	 * Create a test title
	 * @param string $title Text to be used in creating the title.
	 * @return Title
	 */
	private function createTestTitle( $titleName, $namespace = NS_MAIN ) {
		return Title::newFromText( $titleName, $namespace );
	}

	/**
	 * @covers \MediaWiki\MassMessage\SpamlistLookup::factory
	 */
	public function testFactoryForCategorySpamlistLookup() {
		$title = $this->createTestTitle( 'Title_NC', NS_CATEGORY );
		$expected = new CategorySpamlistLookup( $title );
		$actual = SpamlistLookup::factory( $title );

		$this->assertEquals( $expected, $actual );
	}

	/**
	 * @covers \MediaWiki\MassMessage\SpamlistLookup::factory
	 */
	public function testFactoryForListContentSpamlistLookup() {
		$title = $this->createTestTitle( 'Title_MMLC' );
		$expected = new ListContentSpamlistLookup( $title );

		$title->setContentModel( 'MassMessageListContent' );
		$actual = SpamlistLookup::factory( $title );

		$this->assertEquals( $expected, $actual );
	}

	/**
	 * @covers \MediaWiki\MassMessage\SpamlistLookup::factory
	 */
	public function testFactoryForParserFunctionSpamlistLookup() {
		$title = $this->createTestTitle( 'Title_CMW' );
		$expected = new ParserFunctionSpamlistLookup( $title );

		$title->setContentModel( CONTENT_MODEL_WIKITEXT );
		$actual = SpamlistLookup::factory( $title );

		$this->assertEquals( $expected, $actual );
	}

	/**
	 * Test an edge case to return null. We need to explicitly set
	 * content model. As by default, titles are created with content
	 * model CONTENT_MODEL_WIKITEXT which will fail this test if not
	 * explicitly changed to something that will return NULL when
	 * SpamlistLookup::factory is called.
	 * @covers \MediaWiki\MassMessage\SpamlistLookup::factory
	 */
	public function testFactoryThatReturnsNull() {
		// Use NS not accepted in this case. factory() only accepts on NS_CATEGORY
		$title1 = $this->createTestTitle( 'Title_EC' );
		// Set a content invalid model not accepted by ::factory()
		$title1->setContentModel( 'UnacceptableModel' );
		$actual = SpamlistLookup::factory( $title1 );

		$this->assertNull( $actual );

		$title2 = Title::newMainPage();
		// Use a valid content model but not accepted by ::factory()
		$title2->setContentModel( CONTENT_MODEL_CSS );
		$actual = SpamlistLookup::factory( $title2 );

		$this->assertNull( $actual );
	}
}

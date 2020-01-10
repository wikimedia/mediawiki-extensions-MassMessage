<?php

namespace MediaWiki\MassMessage;

use ContentHandler;
use Title;
use WikiPage;

/**
 * Tests for MassMessage List Content related to target processing
 *
 * @covers \MediaWiki\MassMessage\ListContentSpamlistLookup
 * @covers \MediaWiki\MassMessage\SpamlistLookup
 */
class ListContentSpamlistLookupTest extends MassMessageTestCase {

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
		$this->assertEquals( wfWikiID(), $targets[0]['wiki'] );
		$this->assertEquals( 'B', $targets[1]['title'] );
		$this->assertEquals( 'enwiki', $targets[1]['wiki'] );
		$this->assertEquals( 'en.wikipedia.org', $targets[1]['site'] );
	}

	/**
	 * @param int $namespace
	 * @param string $contentModel
	 *
	 * @return Title
	 */
	private function createTestTitle( $namespace = NS_MAIN, $contentModel = CONTENT_MODEL_WIKITEXT ) {
		$title = Title::newFromText( 'MassMessageTestTitle', $namespace );
		$title->setContentModel( $contentModel );
		return $title;
	}

	public function testFactoryForCategorySpamlistLookup() {
		$title = $this->createTestTitle( NS_CATEGORY );
		$expected = new CategorySpamlistLookup( $title );

		$actual = SpamlistLookup::factory( $title );

		$this->assertEquals( $expected, $actual );
	}

	public function testFactoryForListContentSpamlistLookup() {
		$title = $this->createTestTitle( NS_MAIN, 'MassMessageListContent' );
		$expected = new ListContentSpamlistLookup( $title );

		$actual = SpamlistLookup::factory( $title );

		$this->assertEquals( $expected, $actual );
	}

	public function testFactoryForParserFunctionSpamlistLookup() {
		$title = $this->createTestTitle();
		$expected = new ParserFunctionSpamlistLookup( $title );

		$actual = SpamlistLookup::factory( $title );

		$this->assertEquals( $expected, $actual );
	}

	public function testUnacceptableContentModels() {
		$title1 = $this->createTestTitle( NS_MAIN, 'NonExistingContentModel' );
		$actual = SpamlistLookup::factory( $title1 );

		$this->assertNull( $actual );

		$title2 = $this->createTestTitle( NS_MAIN, CONTENT_MODEL_CSS );
		$actual = SpamlistLookup::factory( $title2 );

		$this->assertNull( $actual );
	}

}

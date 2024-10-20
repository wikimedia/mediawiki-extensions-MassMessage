<?php

namespace MediaWiki\MassMessage\Lookup;

use MediaWiki\Content\ContentHandler;
use MediaWiki\MassMessage\MassMessageTestCase;
use MediaWiki\Title\Title;
use MediaWiki\WikiMap\WikiMap;

/**
 * Tests for MassMessage List Content related to target processing
 *
 * @group Database
 *
 * @covers \MediaWiki\MassMessage\Lookup\ListContentSpamlistLookup
 * @covers \MediaWiki\MassMessage\Lookup\SpamlistLookup
 */
class ListContentSpamlistLookupTest extends MassMessageTestCase {

	public function setUp(): void {
		parent::setUp();
		// FIXME: SiteConfigurations are not being taken into account when fetching spam list targets
		$this->mergeMwGlobalArrayValue(
			'wgMassMessageWikiAliases', [
				'en.wikipedia.org' => 'enwiki'
			]
		);
	}

	public function testGetTargets() {
		$text = '{"description":"","targets":['
			. '{"title":"A"},'
			. '{"title":"B","site":"en.wikipedia.org"},'
			. '{"title":"C","site":"invalid.org"}'
			. ']}';
		$content = ContentHandler::makeContent( $text, null, 'MassMessageListContent' );
		$title = Title::newFromText( 'MassMessageListContent_spamlist' );
		$page = $this->getServiceContainer()->getWikiPageFactory()->newFromTitle( $title );
		$page->doUserEditContent(
			$content,
			$this->getTestUser()->getUser(),
			'summary'
		);
		$targets = SpamlistLookup::getTargets( $title );

		$this->assertCount( 2, $targets );
		$this->assertEquals( 'A', $targets[0]['title'] );
		$this->assertEquals( WikiMap::getCurrentWikiId(), $targets[0]['wiki'] );
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

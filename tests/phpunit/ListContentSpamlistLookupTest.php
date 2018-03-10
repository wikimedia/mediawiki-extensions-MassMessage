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
	public function testGeTargets() {
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
}

<?php

namespace MediaWiki\MassMessage;

use Title;
use Wikipage;
use WikitextContent;

/**
 * Tests for Category Spamlist function related to target processing
 */
class CategorySpamlistLookupTest extends MassMessageTestCase {

	/**
	 * @covers \MediaWiki\MassMessage\SpamlistLookup::getTargets
	 * @covers \MediaWiki\MassMessage\CategorySpamlistLookup::fetchTargets
	 */
	public static function testGetTargets() {
		$page = Title::makeTitle( NS_TALK, 'Testing1234' );
		$wikipage = WikiPage::factory( $page );
		$wikipage->doEditContent( new WikitextContent( '[[Category:Spamlist1234]]' ), 'edit summary' );

		$cat = Title::makeTitle( NS_CATEGORY, 'Spamlist1234' );
		$targets = SpamlistLookup::getTargets( $cat );
		self::assertCount( 1, $targets );
		$values = array_values( $targets );
		self::assertEquals( 'Talk:Testing1234', $values[0]['title'] );
	}
}

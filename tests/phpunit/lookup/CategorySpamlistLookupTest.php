<?php

namespace MediaWiki\MassMessage\Lookup;

use MediaWiki\Content\WikitextContent;
use MediaWiki\MassMessage\MassMessageTestCase;
use MediaWiki\Title\Title;

/**
 * Tests for Category Spamlist function related to target processing
 *
 * @group Database
 */
class CategorySpamlistLookupTest extends MassMessageTestCase {

	/**
	 * @covers \MediaWiki\MassMessage\Lookup\SpamlistLookup::getTargets
	 * @covers \MediaWiki\MassMessage\Lookup\CategorySpamlistLookup::fetchTargets
	 */
	public function testGetTargets() {
		$page = Title::makeTitle( NS_TALK, 'Testing1234' );
		$wikipage = $this->getServiceContainer()->getWikiPageFactory()->newFromTitle( $page );
		$wikipage->doUserEditContent(
			new WikitextContent( '[[Category:Spamlist1234]]' ),
			$this->getTestUser()->getUser(),
			'edit summary'
		);

		$cat = Title::makeTitle( NS_CATEGORY, 'Spamlist1234' );
		$targets = SpamlistLookup::getTargets( $cat );
		$this->assertCount( 1, $targets );
		$values = array_values( $targets );
		$this->assertEquals( 'Talk:Testing1234', $values[0]['title'] );
	}
}

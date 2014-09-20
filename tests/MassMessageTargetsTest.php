<?php

/**
 * Tests for functions related to target processing
 *
 * @group Database
 */

class MassMessageTargetsTest extends MassMessageTestCase {

	/**
	 * First value is the page text to create
	 * Second is the values we should check in the first array
	 * @return array
	 */
	public static function provideGetParserFunctionTargets() {
		global $wgContLang;
		$proj = $wgContLang->getFormattedNsText( NS_PROJECT ); // Output changes based on wikiname

		return array(
			// project page, no site provided
			array( '{{#target:Project:Example}}', array( 'title' => $proj . ':Example' ), ),
			// user talk page, no site provided
			array( '{{#target:User talk:Example}}', array('title' => 'User talk:Example' ), ),
			// local redirect being followed
			array( '{{#target:User talk:Is a redirect}}', array('title' => 'User talk:Redirect target' ) ),
			// invalid titles
			array( '{{#target:User:<><}}', array(), ),
			array( '{{#target:Project:!!!<><><><>', array(), ),
			// project page and site
			array( '{{#target:Project:Testing|en.wikipedia.org}}', array( 'title' => 'Project:Testing', 'site' => 'en.wikipedia.org', 'wiki' => 'enwiki' ), ),
			// user page and site
			array( '{{#target:User talk:Test|fr.wikipedia.org}}', array( 'title' => 'User talk:Test', 'site' => 'fr.wikipedia.org', 'wiki' => 'frwiki' ), ),
		);
	}

	/**
	 * @covers MassMessageTargets::getTargets
	 * @covers MassMessageTargets::normalizeTargets
	 * @covers MassMessageTargets::getParserFunctionTargets
	 * @dataProvider provideGetParserFunctionTargets
	 * @param string $text Text of the page to create
	 * @param array $check Stuff to check against
	 */
	public function testGetParserFunctionTargets( $text, $check ) {
		$title = Title::newFromText( 'Input list' );
		MassMessageTest::updatePage( $title, $text );
		$data = MassMessageTargets::normalizeTargets(
			MassMessageTargets::getTargets( $title )
		);

		if ( empty( $check ) ) {
			// Check that the spamlist is empty
			$this->assertTrue( empty( $data ) );
		} else {
			$data = array_values( $data );
			$data = $data[0]; // We're just testing the first value
			foreach ( $check as $key => $value ) {
				$this->assertEquals( $value, $data[$key] );
			}
			if ( !isset( $check['wiki'] ) ) {
				$this->assertEquals( wfWikiID(), $data['wiki'] );
				// Using wfWikiId() within @dataProviders returns a different result
				// than when we use wfWikiId() within a test
			}
		}
	}

	/**
	 * @covers MassMessageTargets::getTargets
	 * @covers MassMessageTargets::getCategoryTargets
	 */
	public function testGetCategoryTargets() {
		$page = Title::newFromText( 'Talk:Testing1234' );
		$wikipage = WikiPage::factory( $page );
		$wikipage->doEditContent( new WikitextContent( '[[Category:Spamlist1234]]' ), 'edit summary' );

		$cat = Title::newFromText( 'Category:Spamlist1234' );
		$targets = MassMessageTargets::getTargets( $cat );
		$this->assertEquals( 1, count( $targets ) );
		$values = array_values( $targets );
		$this->assertEquals( 'Talk:Testing1234', $values[0]['title'] );
	}

	/**
	 * @covers MassMessageTargets::getTargets
	 * @covers MassMessageTargets::getMassMessageListContentTargets
	 */
	public function testGetMassMessageListContentTargets() {
		$text = '{"description":"","targets":['
			. '{"title":"A"},'
			. '{"title":"B","site":"en.wikipedia.org"},'
			. '{"title":"C","site":"invalid.org"}'
			. ']}';
		$content = ContentHandler::makeContent( $text, null, 'MassMessageListContent' );
		$title = Title::newFromText( 'MassMessageListContent_spamlist' );
		$page = WikiPage::factory( $title );
		$page->doEditContent( $content, 'summary' );
		$targets = MassMessageTargets::getTargets( $title );
		$this->assertEquals( 2, count( $targets ) );
		$this->assertEquals( 'A', $targets[0]['title'] );
		$this->assertEquals( wfWikiId(), $targets[0]['wiki'] );
		$this->assertEquals( 'B', $targets[1]['title'] );
		$this->assertEquals( 'enwiki', $targets[1]['wiki'] );
		$this->assertEquals( 'en.wikipedia.org', $targets[1]['site'] );
	}
}

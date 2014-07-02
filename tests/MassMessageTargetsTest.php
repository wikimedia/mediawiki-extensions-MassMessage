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
	 * @covers MassMessageTargets::getParserFunctionTargets
	 * @dataProvider provideGetParserFunctionTargets
	 * @param  string $text  Text of the page to create
	 * @param  array $check Stuff to check against
	 */
	public function testGetParserFunctionTargets( $text, $check ) {
		$title = Title::newFromText( 'Input list ');
		MassMessageTest::updatePage( $title, $text );
		$data = MassMessageTargets::normalizeTargets(
			MassMessageTargets::getParserFunctionTargets( $title, RequestContext::getMain() )
		);

		if ( empty( $check ) ) {
			// Check that the spamlist is empty
			$this->assertTrue( empty( $data ) );
		} else {
			$data = array_values( $data );
			$data = $data[0]; // We're just testing the first value
			foreach ( $check as $key => $value ) {
				$this->assertEquals( $data[$key], $value );
			}
			if ( !isset( $check['wiki'] ) ) {
				$this->assertEquals( $data['wiki'], wfWikiID() );
				// Using wfWikiId() within @dataProviders returns a different result
				// than when we use wfWikiId() within a test
			}
		}
	}

	/**
	 * @covers MassMessageTargets::getCategoryTargets
	 */
	public function testCategorySpamlist() {
		$page = Title::newFromText( 'Talk:Testing1234' );
		$wikipage = WikiPage::factory( $page );
		$wikipage->doEditContent( new WikitextContent( '[[Category:Spamlist1234]]' ), 'edit summary' );

		$cat = Title::newFromText( 'Category:Spamlist1234' );
		$targets = MassMessageTargets::getCategoryTargets( $cat );
		$this->assertEquals( count( $targets ), 1 );
		$values = array_values( $targets );
		$this->assertEquals( $values[0]['title'], 'Talk:Testing1234' );
	}
}

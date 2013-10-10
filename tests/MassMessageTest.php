<?php

/**
 * Tests for the MassMessage extension...
 *
 * @group Database
 */

class MassMessageTest extends MediaWikiTestCase {
	protected function setUp() {
		// $wgConf ewwwww
		global $wgConf, $wgLocalDatabases;
		$wgConf = new SiteConfiguration;
		$wgConf->wikis = array( 'enwiki', 'dewiki', 'frwiki', 'wiki' );
		$wgConf->suffixes = array( 'wiki' );
		$wgConf->settings = array(
			'wgServer' => array(
				'enwiki' => '//en.wikipedia.org',
				'dewiki' => '//de.wikipedia.org',
				'frwiki' => '//fr.wikipedia.org',
			),
		);
		$wgLocalDatabases =& $wgConf->getLocalDatabases();

		// Create a redirect
		$r = Title::newFromText( 'User talk:Redirect target' );
		self::updatePage( $r, 'blank' );
		$r2 = Title::newFromText( 'User talk:Is a redirect' );
		self::updatePage( $r2, '#REDIRECT [[User talk:Redirect target]]' );
		parent::setUp();
	}

	/**
	 * Updates $title with the provided $text
	 * @param Title title
	 * @param string $text
	 */
	public static function updatePage( $title, $text ) {
		$user = new User();
		$page = WikiPage::factory( $title );
		$content = ContentHandler::makeContent( $text, $page->getTitle() );
		$page->doEditContent( $content, "summary", 0, false, $user );
	}

	public static function provideGetDBName() {
		return array(
			array( 'en.wikipedia.org', 'enwiki' ),
			array( 'fr.wikipedia.org', 'frwiki' ),
			array( 'de.wikipedia.org', 'dewiki' ),
		);
	}
	/**
	 * @covers MassMessage::getDBName
	 * @dataProvider provideGetDBName
	 * @param $url
	 * @param $expected
	 */
	public function testGetDBName( $url, $expected ) {
		$dbname = MassMessage::getDBName( $url );
		$this->assertEquals( $dbname, $expected );
	}

	/**
	 * Runs a job to edit the given title
	 * @param $title Title
	 */
	public static function simulateJob( $title ) {
		$params = array( 'subject' => 'Subject line', 'message' => 'This is a message.', );
		$params['comment'] = array( User::newFromName('Admin'), 'metawiki', 'http://meta.wikimedia.org/wiki/Spamlist' );
		$job = new MassMessageJob( $title, $params );
		$job->run();
	}

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
	 * @covers MassMessage::getParserFunctionTargets
	 * @dataProvider provideGetParserFunctionTargets
	 * @param  string $text  Text of the page to create
	 * @param  array $check Stuff to check against
	 */
	public function testGetParserFunctionTargets( $text, $check ) {
		$title = Title::newFromText( 'Input list ');
		self::updatePage( $title, $text );
		$data = MassMessage::getParserFunctionTargets( $title, RequestContext::getMain() );
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
	 * First parameter is the raw url to parse, second is expected output
	 * @return array
	 */
	public static function provideGetBaseUrl() {
		return array(
			array( 'http://en.wikipedia.org', 'en.wikipedia.org' ),
			array( 'https://en.wikipedia.org/wiki/Blah', 'en.wikipedia.org' ),
			array( '//test.wikidata.org/wiki/User talk:Example', 'test.wikidata.org' ),
		);
	}

	/**
	 * @covers MassMessage::getBaseUrl
	 * @dataProvider provideGetBaseUrl
	 * @param  string $url      raw url to parse
	 * @param  string $expected expected value
	 */
	public function testGetBaseUrl( $url, $expected ) {
		$output = MassMessage::getBaseUrl( $url );
		$this->assertEquals( $output, $expected );
	}

	public static function provideGetMessengerUser() {
		return array(
			array( 'MessengerBot' ),
			array( 'EdwardsBot' ),
			array( 'Blah blah blah' ),
		);
	}

	/**
	 * @covers MassMessage::getMessengerUser
	 * @dataProvider provideGetMessengerUser
	 * @param $name
	 */
	public function testGetMessengerUser( $name ) {
		global $wgMassMessageAccountUsername;
		$wgMassMessageAccountUsername = $name;
		$user = MassMessage::getMessengerUser();
		$this->assertEquals( $user->getName(), $name );
		$this->assertTrue( in_array( 'bot' , $user->getGroups() ) );
		$this->assertEquals( $user->mPassword, '' );
	}

	/**
	 * @covers MassMessage::followRedirect
	 */
	public function testFollowRedirect() {
		$title = Title::newfromtext( 'R1' );
		self::updatePage( $title, '#REDIRECT [[R2]]' );
		$title2 = Title::newfromtext( 'R2' );
		self::updatePage( $title2, 'foo' );

		$this->assertEquals( $title2->getFullText(), MassMessage::followRedirect( $title )->getFullText() );
		$this->assertEquals( $title2->getFullText(), MassMessage::followRedirect( $title2 )->getFullText() );
	}

	/**
	 * @covers MassMessageJob::sendMessage
	 * @covers MassMessageJob::editPage
	 */
	public function testMessageSending() {
		$target = Title::newFromText( 'Project:Testing1234' );
		if ( $target->exists() ) {
			// Clear it
			$wikipage = WikiPage::factory( $target );
			$wikipage->doDeleteArticleReal( 'reason' );
		}
		self::simulateJob( $target );
		$target = Title::newFromText( 'Project:Testing1234' ); // Clear cache?
		//$this->assertTrue( $target->exists() ); // Message was created
		$text = WikiPage::factory( $target )->getContent( Revision::RAW )->getNativeData();
		$this->assertEquals( $text, "== Subject line ==\n\nThis is a message.\n<!-- Message sent by User:Admin@metawiki using the list at http://meta.wikimedia.org/wiki/Spamlist -->" );

	}

	/**
	 * @covers MassMessageJob::isOptedOut
	 */
	public function testOptOut() {
		$target = Title::newFromText( 'Project:Opt out test page' );
		self::updatePage( $target, '[[Category:Opted-out of message delivery]]');
		$this->assertTrue( MassMessageJob::isOptedOut( $target ) );
		$this->assertFalse( MassMessageJob::isOptedOut( Title::newFromText( 'Project:Some random page' ) ) );
		self::simulateJob( $target ); // Try posting a message to this page
		$text = WikiPage::factory( $target )->getContent( Revision::RAW )->getNativeData();
		$this->assertEquals( $text, '[[Category:Opted-out of message delivery]]' ); // Nothing should be updated
	}
}

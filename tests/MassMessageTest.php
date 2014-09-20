<?php

/**
 * Tests for the MassMessage extension...
 *
 * @group Database
 */

class MassMessageTest extends MassMessageTestCase {

	public static function provideGetDBName() {
		return array(
			array( 'en.wikipedia.org', 'enwiki' ),
			array( 'fr.wikipedia.org', 'frwiki' ),
			array( 'de.wikipedia.org', 'dewiki' ),
			array( 'not.a.wiki.known.to.us', null ),
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
		$this->assertEquals( $expected, $dbname );
	}

	/**
	 * Runs a job to edit the given title
	 * @param $title Title
	 */
	public static function simulateJob( $title ) {
		$subject = md5( MWCryptRand::generateHex( 15 ) );
		$params = array( 'subject' => $subject, 'message' => 'This is a message.', 'title' => $title->getFullText() );
		$params['comment'] = array( User::newFromName('Admin'), 'metawiki', 'http://meta.wikimedia.org/w/index.php?title=Spamlist&oldid=5' );
		$job = new MassMessageJob( $title, $params );
		$job->run();
		return $subject;
	}

	/**
	 * First parameter is the raw url to parse, second is expected output
	 * @return array
	 */
	public static function provideGetBaseUrl() {
		return array(
			array( 'http://en.wikipedia.org', 'en.wikipedia.org' ),
			array( 'https://en.wikipedia.org/wiki/Blah', 'en.wikipedia.org' ),
			array( 'http://en.wikipedia.org:80/wiki/Blah', 'en.wikipedia.org:80' ),
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
		$this->assertEquals( $expected, $output );
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
		$this->setMwGlobals( 'wgMassMessageAccountUsername', $name );
		$user = MassMessage::getMessengerUser();
		$this->assertEquals( $name, $user->getName() );
		$this->assertTrue( in_array( 'bot' , $user->getGroups() ) );
		$this->assertInstanceOf( 'InvalidPassword', $user->getPassword() );
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
		$subj = self::simulateJob( $target );
		$target = Title::newFromText( 'Project:Testing1234' ); // Clear cache?
		//$this->assertTrue( $target->exists() ); // Message was created
		$text = WikiPage::factory( $target )->getContent( Revision::RAW )->getNativeData();
		$this->assertEquals(
			"== $subj ==\n\nThis is a message.\n<!-- Message sent by User:Admin@metawiki using the list at http://meta.wikimedia.org/w/index.php?title=Spamlist&oldid=5 -->",
			$text
		);
	}

	/**
	 * Tests MassMessageJob::sendMessage and MassMessageJob::addLQTThread
	 */
	public function testLQTMessageSending() {
		global $wgContLang;
		$proj = $wgContLang->getFormattedNsText( NS_PROJECT ); // Output changes based on wikiname

		if ( !class_exists( 'LqtDispatch') ) {
			$this->markTestSkipped( "This test requires the LiquidThreads extension" );
		}
		$target = Title::newFromText( 'Project:LQT test' );
		//$this->assertTrue( LqtDispatch::isLqtPage( $target ) ); // Check that it worked
		$subject = self::simulateJob( $target );
		$this->assertTrue( Title::newFromText( 'Thread:' . $proj . ':LQT test/' . $subject )->exists() );
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
		$this->assertEquals( '[[Category:Opted-out of message delivery]]', $text ); // Nothing should be updated
	}
}

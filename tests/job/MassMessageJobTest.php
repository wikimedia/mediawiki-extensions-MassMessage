<?php

class MassMessageJobTest extends MassMessageTestCase {

	/**
	 * Runs a job to edit the given title
	 */
	private function simulateJob( $title ) {
		$subject = md5( MWCryptRand::generateHex( 15 ) );
		$params = array( 'subject' => $subject, 'message' => 'This is a message.', 'title' => $title->getFullText() );
		$params['comment'] = array( User::newFromName('Admin'), 'metawiki', 'http://meta.wikimedia.org/w/index.php?title=Spamlist&oldid=5' );
		$job = new MassMessageJob( $title, $params );
		$job->run();
		return $subject;
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
		$subj = $this->simulateJob( $target );
		$target = Title::newFromText( 'Project:Testing1234' ); // Clear cache?
		//$this->assertTrue( $target->exists() ); // Message was created
		$text = WikiPage::factory( $target )->getContent( Revision::RAW )->getNativeData();
		$this->assertEquals(
			"== $subj ==\n\nThis is a message.\n<!-- Message sent by User:Admin@metawiki using the list at http://meta.wikimedia.org/w/index.php?title=Spamlist&oldid=5 -->",
			$text
		);
	}

	/**
	 * @covers MassMessageJob::addLQTThread
	 * @covers MassMessageJob::sendMessage
	 */
	public function testLQTMessageSending() {
		global $wgContLang;
		$proj = $wgContLang->getFormattedNsText( NS_PROJECT ); // Output changes based on wikiname

		if ( !class_exists( 'LqtDispatch') ) {
			$this->markTestSkipped( "This test requires the LiquidThreads extension" );
		}
		$target = Title::newFromText( 'Project:LQT test' );
		//$this->assertTrue( LqtDispatch::isLqtPage( $target ) ); // Check that it worked
		$subject = $this->simulateJob( $target );
		$this->assertTrue( Title::newFromText( 'Thread:' . $proj . ':LQT test/' . $subject )->exists() );
	}

	/**
	 * @covers MassMessageJob::isOptedOut
	 */
	public function testOptOut() {
		$fakejob = new MassMessageJob( Title::newMainPage(), array() );
		$target = Title::newFromText( 'Project:Opt out test page' );
		self::updatePage( $target, '[[Category:Opted-out of message delivery]]');
		$this->assertTrue( $fakejob->isOptedOut( $target ) );
		$this->assertFalse( $fakejob->isOptedOut( Title::newFromText( 'Project:Some random page' ) ) );
		$this->simulateJob( $target ); // Try posting a message to this page
		$text = WikiPage::factory( $target )->getContent( Revision::RAW )->getNativeData();
		$this->assertEquals( '[[Category:Opted-out of message delivery]]', $text ); // Nothing should be updated
	}

}
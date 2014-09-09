<?php

/**
 * Tests for the API module to edit a MassMessage delivery list
 * @group API
 * @group Database
 * @group medium
 */
class ApiEditMassMessageListTest extends MassMessageApiTestCase {

	protected static $spamlist = 'ApiEditMMListTest_spamlist';

	protected function setUp() {
		parent::setUp();
		$title = Title::newFromText( self::$spamlist );
		$page = WikiPage::factory( $title );
		$content = ContentHandler::getForModelID( 'MassMessageListContent' )->makeEmptyContent();
		$page->doEditContent( $content, 'summary' );
		$this->doLogin();
	}

	public function testAdd() {
		$result = $this->doApiRequestWithToken( array(
			'action' => 'editmassmessagelist',
			'spamlist' => self::$spamlist,
			'add' => 'A|A|A@en.wikipedia.org|_|_|A@invalid.org|_@invalid.org'
		) );
		$expected = array( 'editmassmessagelist' => array(
			'result' => 'Done',
			'added' => array(
				array( 'title' => 'A', 'missing' => '' ),
				array( 'title' => 'A', 'site' => 'en.wikipedia.org' )
			),
			'invalidadd' => array(
				array( '*' => '_', 'invalidtitle' => '' ),
				array( '*' => 'A@invalid.org', 'invalidsite' => '' ),
				array( '*' => '_@invalid.org', 'invalidtitle' => '', 'invalidsite' => '' )
			)
		) );
		$this->assertEquals( $expected, $result[0] );
	}

	public function testRemove() {
		$content = ContentHandler::makeContent(
			'{"description":"","targets":[{"title":"B"},{"title":"A","site":"en.wikipedia.org"}]}',
			null,
			'MassMessageListContent'
		);
		$page = WikiPage::factory( Title::newFromText( self::$spamlist ) );
		$page->doEditContent( $content, 'summary' );
		$result = $this->doApiRequestWithToken( array(
			'action' => 'editmassmessagelist',
			'spamlist' => self::$spamlist,
			'remove' => 'A|A|B|B|A@en.wikipedia.org|_'
		) );
		$expected = array( 'editmassmessagelist' => array(
			'result' => 'Done',
			'removed' => array(
				array( 'title' => 'B' ),
				array( 'title' => 'A', 'site' => 'en.wikipedia.org' )
			),
			'invalidremove' => array(
				'A',
				'_'
			)
		) );
		$this->assertEquals( $expected, $result[0] );
	}

	public function testMixed() {
		$content = ContentHandler::makeContent(
			'{"description":"","targets":[{"title":"B"},{"title":"A","site":"en.wikipedia.org"}]}',
			null,
			'MassMessageListContent'
		);
		$page = WikiPage::factory( Title::newFromText( self::$spamlist ) );
		$page->doEditContent( $content, 'summary' );
		$result = $this->doApiRequestWithToken( array(
			'action' => 'editmassmessagelist',
			'spamlist' => self::$spamlist,
			'add' => 'B|C|D',
			'remove' => 'A@en.wikipedia.org|B|C'
		) );
		$expected = array( 'editmassmessagelist' => array(
			'result' => 'Success',
			'added' => array(
				array( 'title' => 'D', 'missing' => '' )
			),
			'removed' => array(
				array( 'title' => 'B' ),
				array( 'title' => 'A', 'site' => 'en.wikipedia.org' )
			)
		) );
		$this->assertEquals( $expected, $result[0] );
	}
}

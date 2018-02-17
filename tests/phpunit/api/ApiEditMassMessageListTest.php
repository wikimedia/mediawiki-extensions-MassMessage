<?php
namespace MediaWiki\MassMessage;

use Title;
use ContentHandler;
use WikiPage;

/**
 * Tests for the API module to edit a MassMessage delivery list
 * @group API
 * @group Database
 * @group medium
 * @covers \MediaWiki\MassMessage\ApiEditMassMessageList
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
		$result = $this->doApiRequestWithToken( [
			'action' => 'editmassmessagelist',
			'spamlist' => self::$spamlist,
			'add' => 'A|A|A@en.wikipedia.org|_|_|A@invalid.org|_@invalid.org'
		] );
		$expected = [ 'editmassmessagelist' => [
			'result' => 'Done',
			'added' => [
				[ 'title' => 'A', 'missing' => '' ],
				[ 'title' => 'A', 'site' => 'en.wikipedia.org' ]
			],
			'invalidadd' => [
				[ '*' => '_', 'invalidtitle' => '' ],
				[ '*' => 'A@invalid.org', 'invalidsite' => '' ],
				[ '*' => '_@invalid.org', 'invalidtitle' => '', 'invalidsite' => '' ]
			]
		] ];
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
		$result = $this->doApiRequestWithToken( [
			'action' => 'editmassmessagelist',
			'spamlist' => self::$spamlist,
			'remove' => 'A|A|B|B|A@en.wikipedia.org|_'
		] );
		$expected = [ 'editmassmessagelist' => [
			'result' => 'Done',
			'removed' => [
				[ 'title' => 'B' ],
				[ 'title' => 'A', 'site' => 'en.wikipedia.org' ]
			],
			'invalidremove' => [
				'A',
				'_'
			]
		] ];
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
		$result = $this->doApiRequestWithToken( [
			'action' => 'editmassmessagelist',
			'spamlist' => self::$spamlist,
			'add' => 'B|C|D',
			'remove' => 'A@en.wikipedia.org|B|C'
		] );
		$expected = [ 'editmassmessagelist' => [
			'result' => 'Success',
			'added' => [
				[ 'title' => 'D', 'missing' => '' ]
			],
			'removed' => [
				[ 'title' => 'B' ],
				[ 'title' => 'A', 'site' => 'en.wikipedia.org' ]
			]
		] ];
		$this->assertEquals( $expected, $result[0] );
	}
}

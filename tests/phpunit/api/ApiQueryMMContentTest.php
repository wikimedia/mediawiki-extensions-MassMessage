<?php
declare( strict_types = 1 );

namespace MediaWiki\MassMessage;

use MediaWiki\MediaWikiServices;
use MediaWiki\Title\Title;

/**
 * Tests for the API module to query the content of a MassMessage delivery list.
 * @group API
 * @group Database
 * @group medium
 * @covers \MediaWiki\MassMessage\Api\ApiQueryMMContent
 */
class ApiQueryMMContentTest extends MassMessageApiTestCase {

	/** @var string */
	protected static $spamlist = 'ApiQueryMMContent_spamlist';
	/** @var int */
	private $pageid;

	protected function setUp(): void {
		parent::setUp();
		$mwService = MediaWikiServices::getInstance();

		$title = Title::newFromText( self::$spamlist );
		$page = $mwService->getWikiPageFactory()->newFromTitle( $title );
		$content = $mwService->getContentHandlerFactory()
			->getContentHandler( 'MassMessageListContent' )
			->makeEmptyContent();

		$page->doUserEditContent( $content, $this->getTestSysop()->getUser(), 'summary' );

		// Needed for later
		$this->pageid = $page->getId();

		$this->mergeMwGlobalArrayValue(
			'wgMassMessageWikiAliases', [
				'en.wikipedia.org' => 'enwiki'
			]
		);
	}

	public function testContent() {
		// Set up the page with a description and targets
		$sysop = $this->getTestSysop()->getUser();
		$this->doApiRequestWithToken( [
			'action' => 'editmassmessagelist',
			'spamlist' => self::$spamlist,
			'add' => 'A|B@en.wikipedia.org',
			'description' => 'Spamlist description goes here'
		], null, $sysop );

		$result = $this->doApiRequest( [
			'action' => 'query',
			'prop' => 'mmcontent',
			'titles' => self::$spamlist
		] );
		$expected = [
			$this->pageid => [
				'pageid' => $this->pageid,
				'ns' => 0,
				'title' => str_replace( '_', ' ', self::$spamlist ),
				'mmcontent' => [
					'description' => 'Spamlist description goes here',
					'targets' => [ 'A', 'B@en.wikipedia.org' ]
				]
			]
		];
		$this->assertEquals( $expected, $result[0]['query']['pages'] );
	}
}

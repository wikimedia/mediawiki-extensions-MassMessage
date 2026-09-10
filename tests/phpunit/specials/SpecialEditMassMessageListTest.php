<?php

namespace MediaWiki\MassMessage\Specials;

use MediaWiki\Content\ContentHandler;
use MediaWiki\MassMessage\MassMessageTestCase;
use MediaWiki\Tests\Specials\SpecialPageTestBase;
use MediaWiki\Title\Title;
use Wikimedia\TestingAccessWrapper;

/**
 * @covers \MediaWiki\MassMessage\Specials\SpecialEditMassMessageList
 *
 * @group Database
 */
class SpecialEditMassMessageListTest extends SpecialPageTestBase {

	protected function setUp(): void {
		parent::setUp();
		// Set up SiteConfiguration so DatabaseLookup::getDatabases() works.
		$this->setMwGlobals( 'wgConf', MassMessageTestCase::getTestSiteConfiguration() );
	}

	protected function newSpecialPage() {
		$services = $this->getServiceContainer();
		return new SpecialEditMassMessageList(
			$services->getUserOptionsLookup(),
			$services->getRestrictionStore(),
			$services->getWatchlistManager(),
			$services->getPermissionManager(),
			$services->getRevisionLookup()
		);
	}

	/**
	 * Test that the special page form renders correctly.
	 */
	public function testExecute() {
		$user = $this->getTestSysop()->getUser();
		$title = Title::makeTitle( NS_MAIN, 'TestMassMessageList' );
		$wikiPage = $this->getServiceContainer()->getWikiPageFactory()->newFromTitle( $title );
		$content = ContentHandler::makeContent(
			'{"description":"test","targets":[]}',
			$title,
			'MassMessageListContent'
		);
		$wikiPage->doUserEditContent( $content, $user, 'Create test list' );

		[ $html ] = $this->executeSpecialPage( 'TestMassMessageList', null, null, $user );
		$this->assertIsString( $html );
		$this->assertStringContainsString( 'mw-massmessage-edit-form', $html );
	}

	/**
	 * Test that onSubmit handles missing 'minor' and 'watch' keys in $data without PHP warnings.
	 */
	public function testOnSubmitWithoutOptionalKeys() {
		$user = $this->getTestSysop()->getUser();
		$title = Title::makeTitle( NS_MAIN, 'TestMassMessageList' );
		$wikiPage = $this->getServiceContainer()->getWikiPageFactory()->newFromTitle( $title );
		$content = ContentHandler::makeContent(
			'{"description":"test","targets":[]}',
			$title,
			'MassMessageListContent'
		);
		$wikiPage->doUserEditContent( $content, $user, 'Create test list' );

		$page = $this->newSpecialPage();
		$testingWrapper = TestingAccessWrapper::newFromObject( $page );
		$testingWrapper->title = $title;

		$data = [
			'description' => 'Updated description',
			'content' => 'Test Target',
			'summary' => 'Edit summary',
		];

		// Before the fix, accessing $data['minor'] or $data['watch'] in onSubmit()
		// would trigger PHP Warning: Undefined array key "minor" / "watch".
		$status = $page->onSubmit( $data );
		$this->assertTrue( $status->isGood() );
	}
}

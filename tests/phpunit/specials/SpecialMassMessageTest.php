<?php

namespace MediaWiki\MassMessage\Specials;

use SpecialPageTestBase;
use Wikimedia\TestingAccessWrapper;

/**
 * @covers \MediaWiki\MassMessage\Specials\SpecialMassMessage
 *
 * @group Database
 */
class SpecialMassMessageTest extends SpecialPageTestBase {

	protected function newSpecialPage() {
		$services = $this->getServiceContainer();
		return TestingAccessWrapper::newFromObject( new SpecialMassMessage(
			$services->get( 'MassMessage:LabeledSectionContentFetcher' ),
			$services->get( 'MassMessage:LocalMessageContentFetcher' ),
			$services->get( 'MassMessage:PageMessageBuilder' ),
			$services->getLintErrorChecker(),
		) );
	}

	public function testGetUnclosedTags() {
		$page = $this->newSpecialPage();

		$wikitext = "test";
		$unclosedTags = $page->getUnclosedTags( $wikitext );
		$this->assertCount( 0, $unclosedTags );

		$wikitext = "<div>tests</div>";
		$unclosedTags = $page->getUnclosedTags( $wikitext );
		$this->assertCount( 0, $unclosedTags );

		$wikitext = "<div>tests";
		$unclosedTags = $page->getUnclosedTags( $wikitext );
		$this->assertEquals( [ '<div>' ], $unclosedTags );

		$wikitext = "tests</div>";
		$unclosedTags = $page->getUnclosedTags( $wikitext );
		$this->assertEquals( [ '</div>' ], $unclosedTags );
	}
}

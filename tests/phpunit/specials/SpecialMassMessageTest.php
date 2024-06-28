<?php

namespace MediaWiki\MassMessage\Specials;

use MediaWiki\MainConfigNames;
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
			$services->get( '_Parsoid' ),
			$services->getParsoidPageConfigFactory(),
			$services->getExtensionRegistry()
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

	public function testCheckForLintErrors() {
		$this->overrideConfigValue( MainConfigNames::ParsoidSettings, [
			'linting' => true
		] );

		$page = $this->newSpecialPage();

		$wikitext = "test";
		$lintErrors = $page->checkForLintErrors( $wikitext );
		$this->assertCount( 0, $lintErrors );

		$wikitext = "<div>tests</div>";
		$lintErrors = $page->checkForLintErrors( $wikitext );
		$this->assertCount( 0, $lintErrors );

		$wikitext = "<div>tests";
		$lintErrors = $page->checkForLintErrors( $wikitext );
		$this->assertCount( 1, $lintErrors );
		$this->assertEquals( 'missing-end-tag', $lintErrors[0]['type'] );

		$wikitext = "tests</div>";
		$lintErrors = $page->checkForLintErrors( $wikitext );
		$this->assertCount( 1, $lintErrors );
		$this->assertEquals( 'stripped-tag', $lintErrors[0]['type'] );
	}

}

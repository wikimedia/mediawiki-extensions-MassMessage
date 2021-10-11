<?php

use MediaWiki\MassMessage\Content\MassMessageListDiffEngine;

/**
 * @covers \MediaWiki\MassMessage\Content\MassMessageListDiffEngine
 */
class MassMessageListDiffEngineTest extends MediaWikiIntegrationTestCase {

	public function testGenerateContentDiffBodyWithWrongContentType() {
		$listDiff = new MassMessageListDiffEngine();
		$content = $this->createMock( Content::class );

		$this->expectException( Exception::class );
		$listDiff->generateContentDiffBody( $content, $content );
	}
}

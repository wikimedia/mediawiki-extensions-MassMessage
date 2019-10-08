<?php

use MediaWiki\MassMessage\MassMessageListDiffEngine;

/**
 * @covers \MediaWiki\MassMessage\MassMessageListDiffEngine
 */
class MassMessageListDiffEngineTest extends MediaWikiTestCase {

	public function testGenerateContentDiffBodyWithWrongContentType() {
		$listDiff = new MassMessageListDiffEngine();
		$content = $this->createMock( Content::class );

		$this->expectException( Exception::class );
		$listDiff->generateContentDiffBody( $content, $content );
	}
}

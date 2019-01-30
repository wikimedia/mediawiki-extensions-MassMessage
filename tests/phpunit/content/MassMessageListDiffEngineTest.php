<?php

use MediaWiki\MassMessage\MassMessageListDiffEngine;

/**
 * @covers \MediaWiki\MassMessage\MassMessageListDiffEngine
 */
class MassMessageListDiffEngineTest extends MediaWikiTestCase {

	use \PHPUnit4And6Compat;

	public function testGenerateContentDiffBodyWithWrongContentType() {
		$listDiff = new MassMessageListDiffEngine();
		$content = $this->createMock( Content::class );

		$this->setExpectedException( Exception::class );
		$listDiff->generateContentDiffBody( $content, $content );
	}
}

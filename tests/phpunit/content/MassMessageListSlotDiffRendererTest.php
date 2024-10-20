<?php

use MediaWiki\Content\Content;
use MediaWiki\Context\RequestContext;
use MediaWiki\MassMessage\Content\MassMessageListContent;
use MediaWiki\MassMessage\Content\MassMessageListContentHandler;
use MediaWiki\MassMessage\Content\MassMessageListSlotDiffRenderer;

/**
 * @covers \MediaWiki\MassMessage\Content\MassMessageListSlotDiffRenderer
 */
class MassMessageListSlotDiffRendererTest extends MediaWikiIntegrationTestCase {

	public function testGenerateContentDiffBodyWithWrongContentType() {
		$content1 = $this->createMock( Content::class );
		$content2 = $this->createMock( MassMessageListContent::class );
		$listDiff = new MassMessageListSlotDiffRenderer(
			$this->createMock( TextSlotDiffRenderer::class ),
			$this->createMock( MessageLocalizer::class )
		);

		$this->expectException( IncompatibleDiffTypesException::class );
		$listDiff->getDiff( $content1, $content2 );
	}

	public static function provideGetDiff() {
		$data1 = [
			'description' => 'Desc 1',
			'targets' => [],
		];
		$data2 = [
			'description' => 'Desc 2',
			'targets' => [],
		];
		$data3 = [
			'description' => 'Desc 2',
			'targets' => [ [ 'title' => 'Test' ] ],
		];
		return [
			'empty diff' => [
				$data1,
				$data1,
				'^$',
				null,
			],
			'1..2' => [
				$data1,
				$data2,
				'\(massmessage-diff-descheader\)',
				'\(massmessage-diff-targetsheader\)',
			],
			'2..3' => [
				$data2,
				$data3,
				'\(massmessage-diff-targetsheader\)',
				'\(massmessage-diff-descheader\)',
			],
			'1..3' => [
				$data1,
				$data3,
				'\(massmessage-diff-descheader\).*\(massmessage-diff-targetsheader\)',
				null,
			],
		];
	}

	/**
	 * @dataProvider provideGetDiff
	 * @param array $data1
	 * @param array $data2
	 * @param string $expectMatch
	 * @param string|null $expectNoMatch
	 */
	public function testGetDiff( $data1, $data2, $expectMatch, $expectNoMatch ) {
		$handler = new MassMessageListContentHandler();
		$content1 = $handler->unserializeContent( json_encode( $data1 ) );
		$content2 = $handler->unserializeContent( json_encode( $data2 ) );
		$context = RequestContext::getMain();
		$context->setLanguage( 'qqx' );
		$sdr = $handler->getSlotDiffRenderer( $context );
		$this->assertInstanceOf( MassMessageListSlotDiffRenderer::class, $sdr );
		$result = $sdr->getDiff( $content1, $content2 );
		$this->assertMatchesRegularExpression( '{' . $expectMatch . '}s', $result );
		if ( $expectNoMatch !== null ) {
			$this->assertDoesNotMatchRegularExpression(
				'{' . $expectNoMatch . '}s', $result );
		}
	}
}

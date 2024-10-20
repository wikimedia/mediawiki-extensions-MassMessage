<?php

namespace MediaWiki\MassMessage\Content;

use MediaWiki\Content\Content;
use MediaWiki\Context\IContextSource;
use MediaWiki\Html\Html;
use MediaWiki\Output\OutputPage;
use MediaWiki\Title\Title;
use MessageLocalizer;
use TextSlotDiffRenderer;

class MassMessageListSlotDiffRenderer extends \SlotDiffRenderer {
	/** @var TextSlotDiffRenderer */
	private $textSlotDiffRenderer;

	/** @var MessageLocalizer */
	private $localizer;

	/**
	 * @param TextSlotDiffRenderer $textSlotDiffRenderer
	 * @param MessageLocalizer $localizer
	 */
	public function __construct(
		TextSlotDiffRenderer $textSlotDiffRenderer,
		MessageLocalizer $localizer
	) {
		$this->textSlotDiffRenderer = $textSlotDiffRenderer;
		$this->localizer = $localizer;
	}

	/**
	 * @param Content|null $oldContent
	 * @param Content|null $newContent
	 * @return false|string
	 */
	public function getDiff( Content $oldContent = null, Content $newContent = null ) {
		$this->normalizeContents( $oldContent, $newContent, [ MassMessageListContent::class ] );
		'@phan-var MassMessageListContent $oldContent'; /** @var MassMessageListContent $oldContent */
		'@phan-var MassMessageListContent $newContent'; /** @var MassMessageListContent $newContent */

		if ( !$oldContent->isValid() || !$newContent->isValid() ) {
			return $this->textSlotDiffRenderer->getTextDiff(
				$oldContent->getText(),
				$newContent->getText()
			);
		}

		$output = '';

		$descDiff = $this->textSlotDiffRenderer->getTextDiff(
			$oldContent->getDescription(),
			$newContent->getDescription()
		);
		if ( trim( $descDiff ) !== '' ) {
			$output .= Html::openElement( 'tr' );
			$output .= Html::openElement( 'td',
				[ 'colspan' => 4, 'id' => 'mw-massmessage-diffdescheader' ] );
			$output .= Html::element( 'h4', [],
				$this->localizer->msg( 'massmessage-diff-descheader' )->text() );
			$output .= Html::closeElement( 'td' );
			$output .= Html::closeElement( 'tr' );
			$output .= $descDiff;
		}

		$targetsDiff = $this->textSlotDiffRenderer->getTextDiff(
			implode( "\n", $oldContent->getTargetStrings() ),
			implode( "\n", $newContent->getTargetStrings() )
		);
		if ( trim( $targetsDiff ) !== '' ) {
			$output .= Html::openElement( 'tr' );
			$output .= Html::openElement( 'td',
				[ 'colspan' => 4, 'id' => 'mw-massmessage-difftargetsheader' ] );
			$output .= Html::element( 'h4', [],
				$this->localizer->msg( 'massmessage-diff-targetsheader' )->text() );
			$output .= Html::closeElement( 'td' );
			$output .= Html::closeElement( 'tr' );
			$output .= $targetsDiff;
		}
		return $output;
	}

	/**
	 * @return string[]
	 */
	public function getExtraCacheKeys() {
		return $this->textSlotDiffRenderer->getExtraCacheKeys();
	}

	/**
	 * @param OutputPage $output
	 */
	public function addModules( OutputPage $output ) {
		$this->textSlotDiffRenderer->addModules( $output );
	}

	/**
	 * @param IContextSource $context
	 * @param Title $newTitle
	 * @return (string|null)[]
	 */
	public function getTablePrefix( IContextSource $context, Title $newTitle ): array {
		return $this->textSlotDiffRenderer->getTablePrefix( $context, $newTitle );
	}
}

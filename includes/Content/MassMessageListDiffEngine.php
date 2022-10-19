<?php

namespace MediaWiki\MassMessage\Content;

use Content;
use DifferenceEngine;
use Exception;
use Html;

/**
 * Difference engine for MassMessageListContent.
 */

class MassMessageListDiffEngine extends DifferenceEngine {

	/**
	 * Implement our own diff rendering.
	 *
	 * @param Content $old Old content
	 * @param Content $new New content
	 *
	 * @return bool|string
	 * @throws Exception If old or new content is not an instance of MassMessageListContent
	 */
	public function generateContentDiffBody( Content $old, Content $new ) {
		if ( !( $old instanceof MassMessageListContent )
			|| !( $new instanceof MassMessageListContent )
		) {
			throw new Exception( 'Cannot diff content types other than MassMessageListContent' );
		}

		if ( !$old->isValid() || !$new->isValid() ) {
			return $this->generateTextDiffBody(
				$old->getText(),
				$new->getText()
			);
		}

		$output = '';

		$descDiff = $this->generateTextDiffBody(
			$old->getDescription(),
			$new->getDescription()
		);
		if ( $descDiff === false ) {
			return false;
		}
		if ( trim( $descDiff ) !== '' ) {
			$output .= Html::openElement( 'tr' );
			$output .= Html::openElement( 'td',
				[ 'colspan' => 4, 'id' => 'mw-massmessage-diffdescheader' ] );
			$output .= Html::element( 'h4', [],
				$this->msg( 'massmessage-diff-descheader' )->text() );
			$output .= Html::closeElement( 'td' );
			$output .= Html::closeElement( 'tr' );
			$output .= $descDiff;
		}

		$targetsDiff = $this->generateTextDiffBody(
			implode( "\n", $old->getTargetStrings() ),
			implode( "\n", $new->getTargetStrings() )
		);
		if ( $targetsDiff === false ) {
			return false;
		}
		if ( trim( $targetsDiff ) !== '' ) {
			$output .= Html::openElement( 'tr' );
			$output .= Html::openElement( 'td',
				[ 'colspan' => 4, 'id' => 'mw-massmessage-difftargetsheader' ] );
			$output .= Html::element( 'h4', [],
				$this->msg( 'massmessage-diff-targetsheader' )->text() );
			$output .= Html::closeElement( 'td' );
			$output .= Html::closeElement( 'tr' );
			$output .= $targetsDiff;
		}
		return $output;
	}
}

<?php
/**
 * Difference engine for MassMessageListContent
 */

class MassMessageListDiffEngine extends DifferenceEngine {

	/**
	 * Implement our own diff rendering.
	 * @param Content $old Old content
	 * @param Content $new New content
	 *
	 * @throws Exception If old or new content is not an instance of MassMessageListContent
	 * @return bool|string
	 */
	public function generateContentDiffBody( Content $old, Content $new ) {
		if ( !( $old instanceOf MassMessageListContent )
			|| !( $new instanceOf MassMessageListContent )
		) {
			throw new Exception( 'Cannot diff content types other than MassMessageListContent' );
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
				array( 'colspan' => 4, 'id' => 'mw-massmessage-diffdescheader' ) );
			$output .= Html::element( 'h4', array(),
				$this->msg( 'massmessage-diff-descheader' )->text() );
			$output .= Html::closeElement( 'td' );
			$output .= Html::closeElement( 'tr' );
			$output .= $descDiff;
		}

		$targetsDiff = $this->generateTextDiffBody(
			implode( $old->getTargetStrings(), "\n" ),
			implode( $new->getTargetStrings(), "\n" )
		);
		if ( $targetsDiff === false ) {
			return false;
		}
		if ( trim( $targetsDiff ) !== '' ) {
			$output .= Html::openElement( 'tr' );
			$output .= Html::openElement( 'td',
				array( 'colspan' => 4, 'id' => 'mw-massmessage-difftargetsheader' ) );
			$output .= Html::element( 'h4', array(),
				$this->msg( 'massmessage-diff-targetsheader' )->text() );
			$output .= Html::closeElement( 'td' );
			$output .= Html::closeElement( 'tr' );
			$output .= $targetsDiff;
		}

		return $output;
	}
}

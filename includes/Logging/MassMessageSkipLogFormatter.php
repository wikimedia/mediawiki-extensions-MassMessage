<?php

namespace MediaWiki\MassMessage\Logging;

use Linker;
use LogFormatter;
use Message;

/**
 * Log formatter for 'skip*' entries on Special:Log/massmessage.
 * Parses the message summary so wikilinks work.
 */

class MassMessageSkipLogFormatter extends LogFormatter {

	/**
	 * @return array
	 * @suppress PhanTypeArraySuspicious,PhanTypeArraySuspiciousNull the parent fills parsedParameters
	 */
	protected function getMessageParameters() {
		if ( $this->parsedParameters !== null ) {
			return $this->parsedParameters;
		}

		parent::getMessageParameters();
		// Format the edit summary using Linker::formatComment so that wikilinks
		// and other simple things get parsed, but no HTML
		$this->parsedParameters[3] = Message::rawParam( Linker::formatComment(
			// @phan-suppress-next-line PhanTypeMismatchArgumentProbablyReal
			$this->parsedParameters[3],
			$this->entry->getTarget()
		) );

		// Bad things happens if the numbers are not in correct order
		ksort( $this->parsedParameters );
		return $this->parsedParameters;
	}
}

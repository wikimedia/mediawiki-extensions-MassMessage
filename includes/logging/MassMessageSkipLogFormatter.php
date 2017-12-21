<?php

/**
 * Log formatter for 'skip*' entries on Special:Log/massmessage
 * Parses the message summary so wikilinks work
 */

namespace MediaWiki\MassMessage;

use Linker;
use Message;
use LogFormatter;

class MassMessageSkipLogFormatter extends LogFormatter {

	/**
	 * @return array
	 */
	protected function getMessageParameters() {
		if ( isset( $this->parsedParameters ) ) {
			return $this->parsedParameters;
		}

		parent::getMessageParameters();
		// Format the edit summary using Linker::formatComment so that wikilinks
		// and other simple things get parsed, but no HTML
		$this->parsedParameters[3] = Message::rawParam( Linker::formatComment(
			$this->parsedParameters[3],
			$this->entry->getTarget()
		) );

		// Bad things happens if the numbers are not in correct order
		ksort( $this->parsedParameters );

		return $this->parsedParameters;
	}
}

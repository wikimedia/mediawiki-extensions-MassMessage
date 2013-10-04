<?php

/**
 * Log formatter for 'send' entries on Special:Log/massmessage
 * This lets us link to the specific revid used to send the message
 */

class MassMessageSendLogFormatter extends LogFormatter {

	protected function getMessageParameters() {
		// First call the main function to load the other values
		parent::getMessageParameters();

		$params = $this->extractParameters();
		// Backwards compat for older log entries
		if ( !isset( $params[3] ) ) {
			return $this->parsedParameters;
		}

		// This will be localized with Linker::link
		// @todo Somehow localize for plaintext messages
		$title = Title::newFromText( 'Special:Permalink/' . $params[3] );

		// Our simple version of LogFormatter::makeLink
		if ( $this->plaintext ) {
			$this->parsedParameters[2] = '[[' . $title->getPrefixedText() . ']]';
		} else {
			$this->parsedParameters[2] = Message::rawParam( Linker::link(
				$title,
				htmlspecialchars( $this->entry->getTarget() )
			) );
		}

		ksort( $this->parsedParameters );
		return $this->parsedParameters;
	}
}

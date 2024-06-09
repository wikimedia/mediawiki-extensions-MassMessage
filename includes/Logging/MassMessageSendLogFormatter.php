<?php

namespace MediaWiki\MassMessage\Logging;

use LogFormatter;
use MediaWiki\Message\Message;
use MediaWiki\SpecialPage\SpecialPage;
use MediaWiki\Title\Title;

/**
 * Log formatter for 'send' entries on Special:Log/massmessage.
 * This lets us link to the specific revid used to send the message.
 */

class MassMessageSendLogFormatter extends LogFormatter {

	/** @inheritDoc */
	protected function getMessageParameters(): array {
		// First call the main function to load the other values
		parent::getMessageParameters();

		$params = $this->extractParameters();
		// Backwards compat for older log entries
		if ( !isset( $params[3] ) ) {
			return $this->parsedParameters;
		}

		$title = SpecialPage::getTitleFor( 'PermanentLink', $params[3] );

		// Our simple version of LogFormatter::makeLink
		if ( $this->plaintext ) {
			$this->parsedParameters[2] = '[[' . $title->getPrefixedText() . ']]';
		} else {
			$linkRenderer = $this->getLinkRenderer();
			$target = $this->entry->getTarget();
			if ( $target->exists() ) {
				$link = Message::rawParam( $linkRenderer->makeLink(
					$title,
					$target->getPrefixedText()
				) );
			} else {
				// If the page has been deleted, just show a redlink (bug 57445)
				$link = Message::rawParam( $linkRenderer->makeLink( $target ) );
			}
			$this->parsedParameters[2] = $link;
		}

		if ( isset( $params[4] ) && $params[4] !== '' ) {
			$pageMessageTitle = Title::newFromText( $params[4] );
			if ( $pageMessageTitle ) {
				$this->parsedParameters[4] = Message::rawParam( $this->makePageLink( $pageMessageTitle ) );
			}
		}

		ksort( $this->parsedParameters );
		return $this->parsedParameters;
	}

	/** @inheritDoc */
	protected function getMessageKey(): string {
		$params = $this->getMessageParameters();
		$key = parent::getMessageKey();
		if ( isset( $params[4] ) && $params[4] !== '' ) {
			return $key . '-page-message';
		}

		return $key;
	}
}

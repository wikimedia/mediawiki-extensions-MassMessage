<?php
declare( strict_types = 1 );

namespace MediaWiki\MassMessage;

use Html;
use Language;

/**
 * Contains logic to build the message and subject to be posted
 * @author Abijeet Patro
 * @since 2022.01
 * @license GPL-2.0-or-later
 */
class MessageBuilder {
	/**
	 * Strip tildes at the end of the message
	 *
	 * @param string $customMessage
	 * @return string
	 */
	public function stripTildes( string $customMessage ): string {
		$strippedText = rtrim( $customMessage );

		if ( $strippedText
			&& substr( $strippedText, -4 ) === '~~~~'
			&& substr( $strippedText, -5 ) !== '~~~~~'
		) {
			$strippedText = substr( $strippedText, 0, -4 );
		}

		return $strippedText;
	}

	/**
	 * Merge page message passed as message, wrap it in necessary HTML tags / attributes and
	 * adds language tagging if necessary. Includes a comment about who is the sender.
	 *
	 * @param string $customMessageText
	 * @param LanguageAwareText|null $pageContent
	 * @param Language|null $targetLanguage
	 * @param array $commentParams
	 * @return string
	 */
	public function buildMessage(
		string $customMessageText,
		?LanguageAwareText $pageContent,
		?Language $targetLanguage,
		array $commentParams
	): string {
		$trimmedText = rtrim( $customMessageText );
		$fullMessageText = '';

		if ( $pageContent ) {
			$fullMessageText = $this->wrapBasedOnLanguage( $pageContent, $targetLanguage );
		}

		// If either is empty, the extra new lines will be trimmed
		$fullMessageText = trim( $fullMessageText . "\n\n" . $trimmedText );

		// $commentParams will always be present unless we are runnning tests.
		if ( $commentParams ) {
			$commentMessage = wfMessage( 'massmessage-hidden-comment' )->params( $commentParams );
			if ( $targetLanguage ) {
				$commentMessage = $commentMessage->inLanguage( $targetLanguage );
			}
			$fullMessageText .= "\n" . $commentMessage->text();
		}

		return $fullMessageText;
	}

	/**
	 * Compose the subject depending on page subject or subject and target language.
	 *
	 * @param string $customSubject
	 * @param LanguageAwareText|null $pageSubject
	 * @param Language|null $targetPageLanguage
	 * @return string
	 */
	public function buildSubject(
		string $customSubject,
		?LanguageAwareText $pageSubject,
		?Language $targetPageLanguage
	): string {
		if ( $pageSubject ) {
			return $this->wrapBasedOnLanguage( $pageSubject, $targetPageLanguage );
		}

		return rtrim( $customSubject );
	}

	/**
	 * Wraps the page content based on the page content and the target page language
	 *
	 * @param LanguageAwareText $pageContent
	 * @param Language|null $targetLanguage
	 * @return string
	 */
	private function wrapBasedOnLanguage( LanguageAwareText $pageContent, ?Language $targetLanguage ): string {
		$fullMessageText = '';
		if ( !$targetLanguage || $targetLanguage->getCode() !== $pageContent->getLanguageCode() ) {
			// Wrap page contents if it differs from target page's language. Ideally the
			// message contents would be wrapped too, but we do not know its language.
			$fullMessageText .= Html::rawElement(
				'div',
				[
					'lang' => $pageContent->getLanguageCode(),
					'dir' => $pageContent->getLanguageDirection(),
					// This class is needed for proper rendering of list items (and maybe more)
					'class' => 'mw-content-' . $pageContent->getLanguageDirection()
				],
				"\n" . $pageContent->getWikitext() . "\n"
			);
		} else {
			$fullMessageText = $pageContent->getWikitext();
		}

		return $fullMessageText;
	}
}

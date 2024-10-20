<?php
declare( strict_types = 1 );

namespace MediaWiki\MassMessage;

use MediaWiki\Html\Html;
use MediaWiki\Language\Language;

/**
 * Contains logic to build the message and subject to be posted
 * @author Abijeet Patro
 * @since 2022.01
 * @license GPL-2.0-or-later
 */
class MessageBuilder {
	private const USE_INLINE = true;

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
	 * @param string[] $commentParams
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
			$fullMessageText = $this->wrapBasedOnLanguage( $pageContent, $targetLanguage, !self::USE_INLINE );
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
			$strippedPageSubject = new LanguageAwareText(
				$this->sanitizeSubject( $pageSubject->getWikitext() ),
				$pageSubject->getLanguageCode(),
				$pageSubject->getLanguageDirection()
			);

			return $this->wrapBasedOnLanguage( $strippedPageSubject, $targetPageLanguage, self::USE_INLINE );
		}

		return $this->sanitizeSubject( $customSubject );
	}

	/**
	 * Compose the page subject without any HTML wrapping
	 *
	 * @param string $customSubject
	 * @param LanguageAwareText|null $pageSubject
	 * @return string
	 */
	public function buildPlaintextSubject( string $customSubject, ?LanguageAwareText $pageSubject ): string {
		if ( $pageSubject ) {
			return $this->sanitizeSubject( $pageSubject->getWikitext() );
		}

		return $this->sanitizeSubject( $customSubject );
	}

	/**
	 * Remove all newlines in-between content and remove tags
	 *
	 * @param string $subject
	 * @return string
	 */
	private function sanitizeSubject( string $subject ): string {
		return rtrim( strip_tags( str_replace( "\n", '', $subject ) ) );
	}

	/**
	 * Wraps the page content based on the page content and the target page language
	 *
	 * @param LanguageAwareText $pageContent
	 * @param Language|null $targetLanguage
	 * @param bool $useInline
	 * @return string
	 */
	private function wrapBasedOnLanguage(
		LanguageAwareText $pageContent,
		?Language $targetLanguage,
		bool $useInline
	): string {
		if ( $this->needsWrapping( $targetLanguage, $pageContent ) ) {
			// Wrap page contents if it differs from target page's language. Ideally the
			// message contents would be wrapped too, but we do not know its language.
			return $this->wrapContentWithLanguageAttributes( $pageContent, $useInline );
		} else {
			return $pageContent->getWikitext();
		}
	}

	/**
	 * Check if the page contents need to be wrapped
	 * @param Language|null $targetLanguage
	 * @param LanguageAwareText $pageContent
	 * @return bool
	 */
	private function needsWrapping( ?Language $targetLanguage, LanguageAwareText $pageContent ): bool {
		return !$targetLanguage || $targetLanguage->getCode() !== $pageContent->getLanguageCode();
	}

	/**
	 * Wrap contents with language attributes using inline or block elements
	 * @param LanguageAwareText $pageContent
	 * @param bool $useInline
	 * @return string
	 */
	private function wrapContentWithLanguageAttributes( LanguageAwareText $pageContent, bool $useInline ): string {
		$elementToUse = 'div';
		$content = "\n" . $pageContent->getWikitext() . "\n";

		if ( $useInline == self::USE_INLINE ) {
			$elementToUse = 'span';
			$content = $pageContent->getWikitext();
		}

		return Html::rawElement(
			$elementToUse,
			[
				'lang' => $pageContent->getLanguageCode(),
				'dir' => $pageContent->getLanguageDirection()
			],
			$content
		);
	}
}

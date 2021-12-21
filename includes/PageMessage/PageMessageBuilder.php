<?php
declare( strict_types = 1 );

namespace MediaWiki\MassMessage\PageMessage;

use InvalidArgumentException;
use MediaWiki\Languages\LanguageFallback;
use MediaWiki\Languages\LanguageNameUtils;
use MediaWiki\MassMessage\LanguageAwareText;
use MediaWiki\MassMessage\MessageContentFetcher\LabeledSectionContentFetcher;
use MediaWiki\MassMessage\MessageContentFetcher\LocalMessageContentFetcher;
use MediaWiki\MassMessage\MessageContentFetcher\RemoteMessageContentFetcher;
use Status;
use Title;

/**
 * Contains logic to interact with page being sent as a mesasges
 * @author Abijeet Patro
 * @since 2022.01
 * @license GPL-2.0-or-later
 */
class PageMessageBuilder {
	/** @var string */
	private $currentWikiId;
	/** @var LocalMessageContentFetcher */
	private $localMessageContentFetcher;
	/** @var LabeledSectionContentFetcher */
	private $labeledSectionContentFetcher;
	/** @var RemoteMessageContentFetcher */
	private $remoteMessageContentFetcher;
	/** @var LanguageNameUtils */
	private $languageNameUtils;
	/** @var LanguageFallback */
	private $languageFallback;

	public function __construct(
		LocalMessageContentFetcher $localMessageContentFetcher,
		LabeledSectionContentFetcher $labeledSectionContentFetcher,
		RemoteMessageContentFetcher $remoteMessageContentFetcher,
		LanguageNameUtils $languageNameUtils,
		LanguageFallback $languageFallback,
		string $currentWikiId
	) {
		$this->localMessageContentFetcher = $localMessageContentFetcher;
		$this->labeledSectionContentFetcher = $labeledSectionContentFetcher;
		$this->remoteMessageContentFetcher = $remoteMessageContentFetcher;
		$this->languageNameUtils = $languageNameUtils;
		$this->languageFallback = $languageFallback;
		$this->currentWikiId = $currentWikiId;
	}

	/**
	 * Fetch content from a page or section of a page in a wiki.
	 *
	 * @param string $pageMessage
	 * @param string|null $pageSection
	 * @param string $sourceWikiId
	 * @return PageMessageBuilderResult
	 */
	public function getContent(
		string $pageMessage,
		?string $pageSection,
		string $sourceWikiId
	): PageMessageBuilderResult {
		if ( $pageMessage === '' ) {
			throw new InvalidArgumentException( 'Empty page name passed' );
		}

		$pageContentStatus = $this->getPageContent( $pageMessage, $sourceWikiId );
		if ( !$pageContentStatus->isOK() ) {
			return new PageMessageBuilderResult( $pageContentStatus );
		}

		/** @var LanguageAwareText */
		$pageContent = $pageContentStatus->getValue();
		if ( $pageContent->getWikitext() === '' ) {
			return new PageMessageBuilderResult( Status::newFatal( 'massmessage-page-message-empty', $pageMessage ) );
		}

		if ( $pageSection ) {
			$sectionContentStatus = $this->labeledSectionContentFetcher
				->getContent( $pageContent, $pageSection );
			if ( !$sectionContentStatus->isOK() ) {
				return new PageMessageBuilderResult( $sectionContentStatus );
			}

			/** @var LanguageAwareText */
			$sectionContent = $sectionContentStatus->getValue();
			if ( $sectionContent->getWikitext() === '' ) {
				return new PageMessageBuilderResult(
					Status::newFatal( 'massmessage-page-message-empty', $pageMessage )
				);
			}

			return new PageMessageBuilderResult( $sectionContentStatus, $sectionContent );
		}

		return new PageMessageBuilderResult( $pageContentStatus, $pageContent );
	}

	/**
	 * Get content for a target language from wiki, using fallbacks if necessary
	 *
	 * @param string $titleStr
	 * @param string $targetLangCode
	 * @param string $sourceLangCode
	 * @param string|null $pageSection
	 * @param string $sourceWikiId
	 * @return PageMessageBuilderResult Values is LanguageAwareText or null on failure
	 */
	public function getContentWithFallback(
		string $titleStr,
		string $targetLangCode,
		string $sourceLangCode,
		?string $pageSection,
		string $sourceWikiId
	): PageMessageBuilderResult {
		if ( !$this->languageNameUtils->isKnownLanguageTag( $targetLangCode ) ) {
			return new PageMessageBuilderResult( Status::newFatal( 'massmessage-invalid-lang', $targetLangCode ) );
		}

		// Identify languages to fetch
		$fallbackChain = array_merge(
			[ $targetLangCode ],
			$this->languageFallback->getAll( $targetLangCode )
		);

		foreach ( $fallbackChain as $langCode ) {
			$titleStrWithLang = $titleStr . '/' . $langCode;
			$pageMessageBuilderResult = $this->getContent( $titleStrWithLang, $pageSection, $sourceWikiId );

			if ( $pageMessageBuilderResult->isOK() ) {
				return $pageMessageBuilderResult;
			}

			// Got an unknown error, let's stop looking for other fallbacks
			if ( !$this->isNotFoundError( $pageMessageBuilderResult->getStatus() ) ) {
				break;
			}
		}

		// No language or fallback found or there was an error, go with source language
		$langSuffix = '';
		if ( $sourceLangCode ) {
			$langSuffix = "/$sourceLangCode";
		}

		return $this->getContent( $titleStr . $langSuffix, $pageSection, $sourceWikiId );
	}

	/**
	 * Uses the database or API to fetch content based on the wiki.
	 *
	 * @param string $titleStr
	 * @param string $wikiId
	 * @return Status Values is LanguageAwareText or null on failure
	 */
	private function getPageContent( string $titleStr, string $wikiId ): Status {
		$isCurrentWiki = $this->currentWikiId === $wikiId;
		$title = Title::newFromText( $titleStr );
		if ( $title === null ) {
			return Status::newFatal(
				'massmessage-page-message-invalid', $titleStr
			);
		}

		if ( $isCurrentWiki ) {
			return $this->localMessageContentFetcher->getContent( $title );
		}

		return $this->remoteMessageContentFetcher->getContent( $titleStr, $wikiId );
	}

	/**
	 * Checks if a given Status is a not found error.
	 *
	 * @param Status $status
	 * @return bool
	 */
	private function isNotFoundError( Status $status ): bool {
		$notFoundErrors = [
			'massmessage-page-message-not-found', 'massmessage-page-message-not-found-in-wiki'
		];
		$errors = $status->getErrors();
		if ( $errors ) {
			foreach ( $errors as $error ) {
				if ( in_array( $error['message'], $notFoundErrors ) ) {
					return true;
				}
			}
		}

		return false;
	}
}

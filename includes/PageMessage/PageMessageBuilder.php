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
use MediaWiki\Status\Status;
use MediaWiki\Title\Title;

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

	/**
	 * @param LocalMessageContentFetcher $localMessageContentFetcher
	 * @param LabeledSectionContentFetcher $labeledSectionContentFetcher
	 * @param RemoteMessageContentFetcher $remoteMessageContentFetcher
	 * @param LanguageNameUtils $languageNameUtils
	 * @param LanguageFallback $languageFallback
	 * @param string $currentWikiId
	 */
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
	 * Fetch content from a page or section of a page in a wiki to be used as the subject or
	 * in the message body for a MassMessage
	 *
	 * @param string $pageName
	 * @param string|null $pageMessageSection
	 * @param string|null $pageSubjectSection
	 * @param string $sourceWikiId
	 * @return PageMessageBuilderResult
	 */
	public function getContent(
		string $pageName,
		?string $pageMessageSection,
		?string $pageSubjectSection,
		string $sourceWikiId
	): PageMessageBuilderResult {
		if ( $pageName === '' ) {
			throw new InvalidArgumentException( 'Empty page name passed' );
		}

		$pageContentStatus = $this->getPageContent( $pageName, $sourceWikiId );
		if ( !$pageContentStatus->isOK() ) {
			return new PageMessageBuilderResult( $pageContentStatus );
		}

		/** @var LanguageAwareText */
		$pageContent = $pageContentStatus->getValue();
		if ( $pageContent->getWikitext() === '' ) {
			return new PageMessageBuilderResult( Status::newFatal( 'massmessage-page-message-empty', $pageName ) );
		}

		$pageMessage = $pageContent;
		$pageSubject = null;
		$finalStatus = $pageContentStatus;

		if ( $pageMessageSection ) {
			// Include section tags for backwards compatibility.
			// https://phabricator.wikimedia.org/T254481#6865334
			$messageSectionStatus = $this->labeledSectionContentFetcher
				->getContent( $pageContent, $pageMessageSection );
			$pageMessage = $this->parseGetSectionResponse(
				$messageSectionStatus,
				$finalStatus,
				Status::newFatal( 'massmessage-page-message-empty', $pageName )
			);
		}

		if ( $pageSubjectSection ) {
			$subjectSectionStatus = $this->labeledSectionContentFetcher
				->getContentWithoutTags( $pageContent, $pageSubjectSection );
			$pageSubject = $this->parseGetSectionResponse(
				$subjectSectionStatus,
				$finalStatus,
				Status::newFatal( 'massmessage-page-subject-empty', $pageSubjectSection, $pageName )
			);
		}

		return new PageMessageBuilderResult( $finalStatus, $pageMessage, $pageSubject );
	}

	/**
	 * Get content for a target language from wiki, using fallbacks if necessary
	 *
	 * @param string $titleStr
	 * @param string $targetLangCode
	 * @param string $sourceLangCode
	 * @param string|null $pageMessageSection
	 * @param string|null $pageSubjectSection
	 * @param string $sourceWikiId
	 * @return PageMessageBuilderResult Values is LanguageAwareText or null on failure
	 */
	public function getContentWithFallback(
		string $titleStr,
		string $targetLangCode,
		string $sourceLangCode,
		?string $pageMessageSection,
		?string $pageSubjectSection,
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
			$pageMessageBuilderResult = $this->getContent(
				$titleStrWithLang, $pageMessageSection, $pageSubjectSection, $sourceWikiId
			);

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

		return $this->getContent( $titleStr . $langSuffix, $pageMessageSection, $pageSubjectSection, $sourceWikiId );
	}

	/**
	 * Helper method to parse response from get labeled section method and updates the passed status
	 *
	 * @param Status $sectionStatus Status from get labeled section
	 * @param Status $statusToUpdate Status to update
	 * @param Status $emptySectionErrorStatus Fatal status to use if section content is empty
	 * @return LanguageAwareText|null
	 */
	private function parseGetSectionResponse(
		Status $sectionStatus,
		Status $statusToUpdate,
		Status $emptySectionErrorStatus
	): ?LanguageAwareText {
		if ( !$sectionStatus->isOK() ) {
			$statusToUpdate = $statusToUpdate->merge( $sectionStatus );
		} else {
			/** @var LanguageAwareText */
			$sectionContent = $sectionStatus->getValue();
			if ( $sectionContent->getWikitext() === '' ) {
				$statusToUpdate->merge( $emptySectionErrorStatus );
			} else {
				return $sectionContent;
			}
		}
		return null;
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

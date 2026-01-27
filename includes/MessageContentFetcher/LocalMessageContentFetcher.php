<?php
declare( strict_types = 1 );

namespace MediaWiki\MassMessage\MessageContentFetcher;

use MediaWiki\Content\TextContent;
use MediaWiki\MassMessage\LanguageAwareText;
use MediaWiki\Revision\RevisionStore;
use MediaWiki\Revision\SlotRecord;
use MediaWiki\Title\Title;
use MediaWiki\WikiMap\WikiMap;
use StatusValue;

/**
 * Fetches content from the local wiki
 * @author Abijeet Patro
 * @since 2022.01
 * @license GPL-2.0-or-later
 */
class LocalMessageContentFetcher {
	private readonly string $currentWikiId;

	public function __construct(
		private readonly RevisionStore $revisionStore,
	) {
		$this->currentWikiId = WikiMap::getCurrentWikiId();
	}

	/**
	 * Fetch the page content with the given title from the same wiki.
	 *
	 * @param Title $pageTitle
	 * @return StatusValue Value is LanguageAwareText or null on failure
	 */
	public function getContent( Title $pageTitle ): StatusValue {
		if ( !$pageTitle->exists() ) {
			return StatusValue::newFatal(
				'massmessage-page-message-not-found',
				$pageTitle->getPrefixedText(),
				$this->currentWikiId
			);
		}

		$revision = $this->revisionStore->getRevisionByTitle( $pageTitle );

		if ( $revision === null ) {
			return StatusValue::newFatal(
				'massmessage-page-message-no-revision',
				$pageTitle->getPrefixedText()
			);
		}

		$content = $revision->getContent( SlotRecord::MAIN );
		$wikitext = null;
		if ( $content instanceof TextContent ) {
			$wikitext = $content->getText();
		}

		if ( $wikitext === null ) {
			return StatusValue::newFatal(
				'massmessage-page-message-no-revision-content',
				$pageTitle->getPrefixedText(),
				$revision->getId()
			);
		}

		$content = new LanguageAwareText(
			$wikitext,
			$pageTitle->getPageLanguage()->getCode(),
			$pageTitle->getPageLanguage()->getDir()
		);

		return StatusValue::newGood( $content );
	}

	/**
	 * Fetch the page title given the title string
	 *
	 * @param string $title
	 * @return StatusValue
	 */
	public function getTitle( string $title ): StatusValue {
		$pageTitle = Title::newFromText( $title );

		if ( $pageTitle === null ) {
			return StatusValue::newFatal(
				'massmessage-page-message-invalid', $title
			);
		} elseif ( !$pageTitle->exists() ) {
			return StatusValue::newFatal(
				'massmessage-page-message-not-found',
				$pageTitle->getPrefixedText(),
				$this->currentWikiId
			);
		}

		return StatusValue::newGood( $pageTitle );
	}
}

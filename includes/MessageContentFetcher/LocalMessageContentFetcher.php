<?php
declare( strict_types = 1 );

namespace MediaWiki\MassMessage\MessageContentFetcher;

use MediaWiki\Content\TextContent;
use MediaWiki\MassMessage\LanguageAwareText;
use MediaWiki\Revision\RevisionStore;
use MediaWiki\Revision\SlotRecord;
use MediaWiki\Status\Status;
use MediaWiki\Title\Title;
use MediaWiki\WikiMap\WikiMap;

/**
 * Fetches content from the local wiki
 * @author Abijeet Patro
 * @since 2022.01
 * @license GPL-2.0-or-later
 */
class LocalMessageContentFetcher {
	/** @var RevisionStore */
	private $revisionStore;
	/** @var string */
	private $currentWikiId;

	/**
	 * @param RevisionStore $revisionStore
	 */
	public function __construct( RevisionStore $revisionStore ) {
		$this->revisionStore = $revisionStore;
		$this->currentWikiId = WikiMap::getCurrentWikiId();
	}

	/**
	 * Fetch the page content with the given title from the same wiki.
	 *
	 * @param Title $pageTitle
	 * @return Status Value is LanguageAwareText or null on failure
	 */
	public function getContent( Title $pageTitle ): Status {
		if ( !$pageTitle->exists() ) {
			return Status::newFatal(
				'massmessage-page-message-not-found',
				$pageTitle->getPrefixedText(),
				$this->currentWikiId
			);
		}

		$revision = $this->revisionStore->getRevisionByTitle( $pageTitle );

		if ( $revision === null ) {
			return Status::newFatal(
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
			return Status::newFatal(
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

		return Status::newGood( $content );
	}

	/**
	 * Fetch the page title given the title string
	 *
	 * @param string $title
	 * @return Status
	 */
	public function getTitle( string $title ): Status {
		$pageTitle = Title::newFromText( $title );

		if ( $pageTitle === null ) {
			return Status::newFatal(
				'massmessage-page-message-invalid', $title
			);
		} elseif ( !$pageTitle->exists() ) {
			return Status::newFatal(
				'massmessage-page-message-not-found',
				$pageTitle->getPrefixedText(),
				$this->currentWikiId
			);
		}

		return Status::newGood( $pageTitle );
	}
}

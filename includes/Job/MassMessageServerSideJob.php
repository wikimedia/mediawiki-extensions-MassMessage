<?php

namespace MediaWiki\MassMessage\Job;

use Exception;
use Job;
use MediaWiki\MassMessage\MassMessage;
use MediaWiki\MediaWikiServices;
use MediaWiki\Revision\RevisionRecord;
use Status;
use Title;
use WikitextContent;

/**
 * JobQueue class for jobs queued server side.
 *
 * @file
 * @ingroup JobQueue
 * @author Kunal Mehta
 * @license GPL-2.0-or-later
 */

class MassMessageServerSideJob extends MassMessageJob {
	public function __construct( Title $title, array $params ) {
		Job::__construct( 'MassMessageServerSideJob', $title, $params );
		$this->removeDuplicates = true;
	}

	/**
	 * Can't opt-out of these messages!
	 *
	 * @param Title $title
	 * @return bool
	 */
	public function isOptedOut( Title $title ) {
		return false;
	}

	/**
	 * Don't add any hidden comments.
	 *
	 * @param bool $stripTildes ignored
	 * @return Status
	 */
	protected function makeText( bool $stripTildes = false ): Status {
		return Status::newGood( $this->params['message'] );
	}

	protected function editPage( string $text, string $subject ): bool {
		$tries = 1;
		$titleText = $this->title->getPrefixedText();
		$user = MassMessage::getMessengerUser();
		$wikiPageFactory = MediaWikiServices::getInstance()->getWikiPageFactory();
		$text = "== $subject ==\n\n$text";
		while ( true ) {
			$page = $wikiPageFactory->newFromTitle( $this->title );
			$flags = 0;
			if ( $page->exists() ) {
				$oldContent = $page->getContent( RevisionRecord::RAW );
				$text = $oldContent->getNativeData() . "\n\n" . $text;
				$flags |= EDIT_UPDATE;
			} else {
				$flags |= EDIT_NEW;
			}
			if ( $this->title->inNamespace( NS_USER_TALK ) ) {
				$flags |= EDIT_FORCE_BOT;
			}
			$status = $page->doUserEditContent(
				new WikitextContent( $text ),
				$user,
				$subject,
				$flags
			);
			if ( $status->isOK() ) {
				break;
			}

			if ( !$status->hasMessage( 'edit-conflict' ) ) {
				throw new Exception( "Error editing $titleText: {$status->getWikiText()}" );
			}

			if ( $tries > 5 ) {
				throw new Exception(
					"Got 5 edit conflicts when trying to edit $titleText"
				);
			}

			$tries++;
		}

		return true;
	}
}

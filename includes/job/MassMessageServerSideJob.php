<?php

namespace MediaWiki\MassMessage;

use Exception;
use Job;
use Revision;
use Title;
use WikiPage;
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
	 * @return string
	 */
	protected function makeText( $stripTildes = false ) {
		return $this->params['message'];
	}

	protected function editPage() {
		$tries = 1;
		$titleText = $this->title->getPrefixedText();
		$user = MassMessage::getMessengerUser();
		$subject = $this->params['subject'];
		$text = "== $subject ==\n\n{$this->makeText()}";
		while ( true ) {
			$page = WikiPage::factory( $this->title );
			$flags = 0;
			if ( $page->exists() ) {
				$oldContent = $page->getContent( Revision::RAW );
				$text = $oldContent->getNativeData() . "\n\n" . $text;
				$flags |= EDIT_UPDATE;
			} else {
				$flags |= EDIT_NEW;
			}
			if ( $this->title->inNamespace( NS_USER_TALK ) ) {
				$flags |= EDIT_FORCE_BOT;
			}
			$status = $page->doEditContent(
				new WikitextContent( $text ),
				$subject,
				$flags,
				false,
				$user
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
	}
}

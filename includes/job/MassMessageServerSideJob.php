<?php

/**
 * JobQueue class for jobs queued server side
 *
 * @file
 * @ingroup JobQueue
 * @author Kunal Mehta
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */
class MassMessageServerSideJob extends MassMessageJob {
	public function __construct( Title $title, array $params, $id = 0 ) {
		Job::__construct( 'MassMessageServerSideJob', $title, $params, $id );
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
	 * Don't add any hidden comments
	 * @param $stripTildes bool ignored
	 * @return string
	 */
	function makeText( $stripTildes = false ) {
		return $this->params['message'];
	}

	protected function editPage() {
		$user = MassMessage::getMessengerUser();
		$page = WikiPage::factory( $this->title );
		$subject = $this->params['subject'];
		$text = "== $subject ==\n\n{$this->makeText()}";
		$flags = 0;
		if ( $page->exists() ) {
			$oldContent = $page->getContent( Revision::RAW );
			$text = $oldContent->getNativeData() . "\n\n" . $text;
			$flags = $flags | EDIT_UPDATE;
		} else {
			$flags = $flags | EDIT_NEW;
		}
		if ( $this->title->inNamespace( NS_USER_TALK ) ) {
			$flags = $flags | EDIT_FORCE_BOT;
		}
		$status = $page->doEditContent(
			new WikitextContent( $text ),
			$subject,
			$flags,
			false,
			$user
		);
		if ( !$status->isOK() ) {
			$errors = $status->getErrorsArray();
			$this->logLocalFailure( $errors[0] );
		}
	}
}

<?php
/**
 * Job Queue class to send a message to
 * a user.
 * Based on code from TranslationNotifications
 * https://mediawiki.org/wiki/Extension:TranslationNotifications
 *
 * @file
 * @ingroup JobQueue
 * @author Kunal Mehta
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */
class MassMessageJob extends Job {
	public function __construct( $title, $params, $id = 0 ) {
		parent::__construct( 'massmessageJob', $title, $params, $id );
	}

	/**
	 * Execute the job
	 *
	 * @return bool
	 */
	public function run() {
		$status = $this->sendLocalMessage();

		if ( $status !== true ) {
			$this->setLastError( $status );
			return false;
		}

		return true;
	}
	/**
	 * Normalizes the title according to $wgNamespacesToConvert and $wgNamespacesToPostIn
	 * @param  Title $title
	 * @return Title|null null if we shouldn't post on that title
	 */
	function normalizeTitle( $title ) {
		global $wgNamespacesToPostIn, $wgNamespacesToConvert;
		if ( isset( $wgNamespacesToConvert[$title->getNamespace()] ) ) {
			$title = Title::makeTitle( $wgNamespacesToConvert[$title->getNamespace()], $title->getText() );
		}
		if ( !in_array( $title->getNamespace(), $wgNamespacesToPostIn ) ) {
			$title = null;
		}
		return $title;
	}

	/**
	 * Log any message failures on the submission site.
	 *
	 * @param $title Title
	 * @param $subject string
	 * @param $reason string
	 */
	function logLocalFailure( $title, $subject, $reason ) {

		$logEntry = new ManualLogEntry( 'massmessage', 'failure' );
		$logEntry->setPerformer( MassMessage::getMessengerUser() );
		$logEntry->setTarget( $title );
		$logEntry->setComment( $subject ); 
		$logEntry->setParameters( array(
			'4::reason' => $reason,
		) );

		$logid = $logEntry->insert();
		$logEntry->publish( $logid );

	}

	/**
	 * Send a message to a user on the same wiki.
	 * Modified from the TranslationNotification extension
	 *
	 * @return bool
	 */
	function sendLocalMessage() {
		$title = $this->normalizeTitle( $this->title );
		if ( $title === null ) {
			return true; // Skip it
		}

		$text = "== " . $this->params['subject'] . " ==\n\n" . $this->params['message'];

		$talkPage = WikiPage::factory( $title );
		$flags = $talkPage->checkFlags( 0 );
		if ( $flags & EDIT_UPDATE ) {
			$content = $talkPage->getContent( Revision::RAW );
			if ( $content instanceof TextContent ) {
				$textContent = $content->getNativeData();
			} else {
				// Cannot do anything with non-TextContent pages. Shouldn't happen.
				return true;
			}

			$text = $textContent . "\n" . $text;
		}

		// If we're sending to a User talk: page, make sure the user exists.
		// Redirects are automatically followed in getLocalTargets
		if ( $title->getNamespace() == NS_USER_TALK ) {
			$user = User::newFromName( $title->getBaseText() );
			if ( !$user->getId() ) { // Does not exist
				return true; // Should we log anything here?
			}
		}

		// Check that the sender isn't blocked before we send the message
		// This lets a sysop stop the job if needed.
		$user = MassMessage::getMessengerUser();
		if ( $user->isBlocked() ) {
			// Log it so we know which users didn't get the message.
			$this->logLocalFailure( $this->title, $this->params['subject'], 'massmessage-account-blocked' );
			return true;
		}

		// Mark the edit as bot
		$flags = $flags | EDIT_FORCE_BOT;

		$status = $talkPage->doEditContent(
			ContentHandler::makeContent( $text, $this->title ),
			$this->params['subject'],
			$flags,
			false,
			$user
		);

		return $status->isGood();
	}
}

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
		$status = $this->sendMessage();

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
	 * Checks whether the target page is in an opt-out category
	 *
	 * @param $title Title
	 * @return bool
	 */
	public static function isOptedOut( $title) {
		$wikipage = WikiPage::factory( $title );
		$categories = $wikipage->getCategories();
		$category = Title::makeTitle( NS_CATEGORY, wfMessage( 'massmessage-optout-category')->inContentLanguage()->text() );
		foreach ( $categories as $cat ) {
			if ( $category->equals( $cat ) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Log any message failures on the submission site.
	 *
	 * @param $reason string
	 */
	function logLocalFailure( $reason ) {

		$logEntry = new ManualLogEntry( 'massmessage', 'failure' );
		$logEntry->setPerformer( MassMessage::getMessengerUser() );
		$logEntry->setTarget( $this->title );
		$logEntry->setParameters( array(
			'4::subject' => $this->params['subject'],
			'5::reason' => $reason,
		) );

		$logid = $logEntry->insert();
		$logEntry->publish( $logid );
	}

	/**
	 * Send a message to a user
	 * Modified from the TranslationNotification extension
	 *
	 * @return bool
	 */
	function sendMessage() {
		$title = $this->normalizeTitle( $this->title );
		if ( $title === null ) {
			return true; // Skip it
		}

		if ( self::isOptedOut( $this->title ) ) {
			return true; // Oh well.
		}

		// If we're sending to a User:/User talk: page, make sure the user exists.
		// Redirects are automatically followed in getLocalTargets
		if ( $title->getNamespace() == NS_USER || $title->getNamespace() == NS_USER_TALK ) {
			$user = User::newFromName( $title->getBaseText() );
			if ( !$user->getId() ) { // Does not exist
				return true; // Should we log anything here?
			}
		}

		$this->editPage();

		return true;
	}

	function editPage() {
		global $wgUser, $wgRequest;
		$user = MassMessage::getMessengerUser();
		$wgUser = $user; // Is this safe? We need to do this for EditPage.php

		$text = $this->params['message'];
		$text .= "\n" . wfMessage( 'massmessage-hidden-comment' )->params( $this->params['comment'] )->text();

		$api = new ApiMain(
			new DerivativeRequest(
				$wgRequest,
				array(
					'action' => 'edit',
					'title' => $this->title->getPrefixedText(),
					'section' => 'new',
					'summary' => $this->params['subject'],
					'text' => $text,
					'notminor' => true,
					'bot' => true,
					'token' => $user->getEditToken()
				),
				true // was posted?
			),
			true // enable write?
		);
		$api->getContext()->setUser( $user );
		try {
			$api->execute();
		} catch ( UsageException $e ) {
			$this->logLocalFailure( $e->getCodeString() );
		}
	}
}

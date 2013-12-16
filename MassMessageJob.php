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
	public function __construct( Title $title, array $params, $id = 0 ) {
		// Create a fresh Title object so namespaces are evaluated
		// in the context of the target site. See bug 57464.
		// Note that jobs created previously might not have a
		// title param, so check for that.
		if ( isset( $params['title'] ) ) {
			$title = Title::newFromText( $params['title'] );
		}

		parent::__construct( 'MassMessageJob', $title, $params, $id );
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
	function normalizeTitle( Title $title ) {
		global $wgNamespacesToPostIn, $wgNamespacesToConvert;
		if ( isset( $wgNamespacesToConvert[$title->getNamespace()] ) ) {
			$title = Title::makeTitle( $wgNamespacesToConvert[$title->getNamespace()], $title->getText() );
		}
		$title = MassMessage::followRedirect( $title ) ?: $title; // Try to follow redirects
		if ( !in_array( $title->getNamespace(), $wgNamespacesToPostIn ) ) {
			$this->logLocalSkip( 'skipbadns');
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
	public static function isOptedOut( Title $title) {
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
	 * Log any skips on the target site
	 *
	 * @param $reason string log subtype
	 */
	function logLocalSkip( $reason ) {
		$logEntry = new ManualLogEntry( 'massmessage', $reason );
		$logEntry->setPerformer( MassMessage::getMessengerUser() );
		$logEntry->setTarget( $this->title );
		$logEntry->setParameters( array(
			'4::subject' => $this->params['subject']
		) );

		$logid = $logEntry->insert();
		$logEntry->publish( $logid );
	}


	/**
	 * Log any message failures on the target site.
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

		// stick it in the debug log
		$text = 'Target: ' . $this->title->getPrefixedText();
		$text .= ' Subject: ' . $this->params['subject'];
		$text .= ' Reason: ' . $reason;
		wfDebugLog( 'massmessage', $text );
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

		$this->title = $title;

		if ( self::isOptedOut( $this->title ) ) {
			$this->logLocalSkip( 'skipoptout' );
			return true; // Oh well.
		}

		// If we're sending to a User:/User talk: page, make sure the user exists.
		// Redirects are automatically followed in getLocalTargets
		if ( $title->getNamespace() == NS_USER || $title->getNamespace() == NS_USER_TALK ) {
			$user = User::newFromName( $title->getBaseText() );
			if ( !$user->getId() ) { // Does not exist
				$this->logLocalSkip( 'skipnouser' );
				return true;
			}
		}

		// See if we should use LiquidThreads
		if ( class_exists( 'LqtDispatch' ) && LqtDispatch::isLqtPage( $title ) ) { // This is the same check that LQT uses internally
			$this->addLQTThread();
		} else {
			$this->editPage();
		}

		return true;
	}

	function editPage() {
		$user = MassMessage::getMessengerUser();
		$params = array(
			'action' => 'edit',
			'title' => $this->title->getPrefixedText(),
			'section' => 'new',
			'summary' => $this->params['subject'],
			'text' => $this->makeText(),
			'notminor' => true,
			'bot' => true,
			'token' => $user->getEditToken()
		);

		$this->makeAPIRequest( $params );
	}

	function addLQTThread() {
		$user = MassMessage::getMessengerUser();
		$params = array(
			'action' => 'threadaction',
			'threadaction' => 'newthread',
			'talkpage' => $this->title,
			'subject' => $this->params['subject'],
			'text' => $this->makeText(),
			'token' => $user->getEditToken()
		); // LQT will automatically mark the edit as bot if we're a bot

		$this->makeAPIRequest( $params );
	}

	/**
	 * Add some stuff to the end of the message
	 * @return string
	 */
	function makeText() {
		$text = $this->params['message'];
		$text .= "\n" . wfMessage( 'massmessage-hidden-comment' )->params( $this->params['comment'] )->text();
		return $text;
	}

	function makeAPIRequest( array $params ) {
		global $wgUser, $wgRequest;

		$wgRequest = new DerivativeRequest(
			$wgRequest,
			$params,
			true // was posted?
		);
		$wgUser = MassMessage::getMessengerUser();
		// New user objects will use $wgRequest, so we set that
		// to our DerivativeRequest, so we don't run into any issues

		RequestContext::getMain()->setUser( $wgUser );
		RequestContext::getMain()->setRequest( $wgRequest );
		// All further internal API requests will use the main
		// RequestContext, so setting it here will fix it for
		// all other internal uses, like how LQT does

		$api = new ApiMain(
			$wgRequest,
			true // enable write?
		);

		try {
			$api->execute();
		} catch ( UsageException $e ) {
			$this->logLocalFailure( $e->getCodeString() );
		}
	}
}

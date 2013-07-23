<?php

/*
 * Form to allow users to send messages
 * to a lot of users at once.
 * Based on code from TranslationNotifications
 * https://mediawiki.org/wiki/Extension:TranslationNotifications
 *
 * @file
 * @author Kunal Mehta
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

class SpecialMassMessage extends SpecialPage {
	function __construct() {
		parent::__construct( 'MassMessage', 'massmessage' );
	}
 
	function execute( $par ) {
		$request = $this->getRequest();
		$output = $this->getOutput();
		$this->setHeaders();
		$this->outputHeader();
		$this->checkPermissions();
		$context = $this->getContext();
		$form = new HtmlForm( $this->createForm(), $context );
		$form->setId( 'massmessage-form' );
		$form->setSubmitText( $context->msg( 'massmessage-form-submit' )->text() );
		$form->setSubmitId( 'massmessage-submit' );
		$form->setSubmitCallback( array( $this, 'submit' ) );
		
		$form->prepareForm();
		$result = $form->tryAuthorizedSubmit();
		if ( $result === true || ( $result instanceof Status && $result->isGood() ) ) {
			$this->getOutput()->addWikiMsg( 'massmessage-submitted' );
		} elseif ( $result instanceof Status ) {
			$errors = $result->getErrorsArray();
			foreach ( $errors as $msg ) {
				$this->getOutput()->addWikiMsg( $msg );
			}
		} else {
			$form->displayForm( $result );
		}
	}

	function createForm() {
		global $wgUser;
		$request = $this->getRequest();
		$m = array();
		// Who to send to
		$m['spamlist'] = array(
			'id' => 'form-spamlist',
			'type' => 'text',
			'label-message' => 'massmessage-form-spamlist',
			'default' => $request->getText( 'spamlist' )
		);
		// The subject line
		$m['subject'] = array(
			'id' => 'form-subject',
			'type' => 'text',
			'label-message' => 'massmessage-form-subject',
			'default' => $request->getText( 'subject' )
		);

		// The message to send
		$m['message'] = array(
			'id' => 'form-message',
			'type' => 'textarea',
			'label-message' => 'massmessage-form-message',
			'default' => $request->getText( 'message' )
		);

		if ( $wgUser->isAllowed( 'massmessage-global' ) ) {
			$m['global'] = array(
				'id' => 'form-global',
				'type' => 'check',
				'label-message' => 'massmessage-form-global',
				'default' => $request->getText( 'global' ) == 'yes' ? true : false
			);
		}

		return $m;
	}

	/*
	 * Get a list of pages to spam
	 *
	 * @param $spamlist Title
	 * @return Array
	 */
	function getLocalTargets( $spamlist ) {
		// Something.
		global $wgNamespacesToExtractLinksFor, $wgNamespacesToConvert;
		$pageID = $spamlist->getArticleID();
		$namespaces = '(' . implode( ', ', $wgNamespacesToExtractLinksFor ) . ')';
		$dbr = wfGetDB( DB_SLAVE );
		$res = $dbr->select(
			'pagelinks',
			array( 'pl_namespace', 'pl_title' ),
			array( "pl_from=$pageID", "pl_namespace in $namespaces" ),
			__METHOD__,
			array()
		);
		$pages = array();
		foreach ( $res as $row ) {
			$ns = $row->pl_namespace;
			if ( isset( $wgNamespacesToConvert[$ns] ) ) {
				$ns = $wgNamespacesToConvert[$ns];
			}
			$title = Title::makeTitle( $ns, $row->pl_title );
			$title = MassMessage::followRedirect( $title );
			if ( $title !== null ) { // Skip interwiki redirects
				$pages[$title->getFullText()] = $title; // Use an assoc array to quickly and easily filter out duplicates
			}
		}
		return $pages;
	}
	/*
	 * Log the spamming to Special:Log/massmessage
	 *
	 * @param $spamlist Title
	 * @param $subject string
	 */
	function logToWiki( $spamlist, $subject ) {
		global $wgUser;
		// $title->getLatestRevID()
	
		$logEntry = new ManualLogEntry( 'massmessage', 'send' );
		$logEntry->setPerformer( $wgUser );
		$logEntry->setTarget( $spamlist );
		$logEntry->setComment( $subject ); 

		$logid = $logEntry->insert();
		$logEntry->publish( $logid );

	}

	/*
	 * Send out the message
	 *
	 * @param $data Array
	 * @return Status
	 */
	function submit( $data ) {
		// Check that the spamlist exists.
		$spamlist = Title::newFromText( $data['spamlist'] );
		$global = isset( $data['global'] ) && $data['global']; // If the message delivery is global
		$status = new Status();
		$errors = array();
		if ( $spamlist === null || !$spamlist->exists() ) {
			$status->fatal( 'massmessage-spamlist-doesnotexist' );
		} else {
			// Page exists, follow a redirect if possible
			$target = MassMessage::followRedirect( $spamlist );
			if ( $target === null || !$target->exists() ) {
				$status->fatal( 'massmessage-spamlist-doesnotexist' ); // Interwiki redirect or non-existent page.
			} else {
				$spamlist = $target;
			}
		}


		// Check that our account hasn't been blocked.
		$user = MassMessage::getMessengerUser();
		if ( !$global && $user->isBlocked() ) {
			// If our delivery is global, it doesnt matter if our local account is blocked
			$status->fatal( 'massmessage-account-blocked' );
		}

		// If we have any errors, abort.
		if ( !$status->isOK() ) {
			return $status;
		}

		// Log it.
		$this->logToWiki( $spamlist, $data['subject'] );

		// Insert it into the job queue.
		$pages = $this->getLocalTargets( $spamlist );
		foreach ( $pages as $title => $page ) {
			$job = new MassMessageJob( $page, $data );
			JobQueueGroup::singleton()->push( $job );
		}
		return $status;
	}
}

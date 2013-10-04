<?php

/**
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

	/**
	 * @var Status
	 */
	protected $status;

	/**
	 * @var string
	 */
	protected $state;

	/**
	 * @var int
	 */
	protected $count;

	function __construct() {
		parent::__construct( 'MassMessage', 'massmessage' );
	}

	function execute( $par ) {
		$request = $this->getRequest();
		$context = $this->getContext();
		$output = $this->getOutput();

		$output->addModules( 'ext.MassMessage.special.js' );
		$output->addModuleStyles( 'ext.MassMessage.special' );
		$this->setHeaders();
		$this->outputHeader();
		$this->checkPermissions();

		// Some variables...
		$this->status = new Status();

		// Figure out what state we're in.
		if ( $request->getText( 'wpsubmit-button' ) == $context->msg( 'massmessage-form-submit' )->text() ) {
			$this->state = 'submit';
		} elseif ( $request->getText( 'wppreview-button' ) == $context->msg( 'massmessage-form-preview' )->text() ) {
			$this->state = 'preview';
		} else {
			$this->state = 'form';
		}

		$form = new HtmlForm( $this->createForm(), $context );
		$form->setId( 'mw-massmessage-form' );
		$form->setDisplayFormat( 'div' );
		if ( $this->state == 'form' ) {
			$form->addPreText( $context->msg( 'massmessage-form-header' )->parse() );
		}
		$form->setWrapperLegendMsg( 'massmessage' );
		$form->suppressDefaultSubmit(); // We use our own buttons.
		$form->setSubmitCallback( array( $this, 'callback' ) );
		$form->setMethod( 'post' );

		$form->prepareForm();
		$result = $form->tryAuthorizedSubmit();
		if ( $result === true || ( $result instanceof Status && $result->isGood() ) ) {
			if ( $this->state == 'submit' ) { // If it's preview, everything is shown already.
				$msg = $this->msg( 'massmessage-submitted' )->params( $this->count )->plain();
				$output->addWikiText( $msg );
				$output->addWikiMsg( 'massmessage-nextsteps' );
			}
		} else {
			$form->displayForm( $result );
		}
	}

	/**
	 * @return string
	 */
	function getState() {
		return $this->state;
	}

	/**
	 * @return Status
	 */
	function getStatus() {
		return $this->status;
	}

	/**
	 * Note that this won't be initalized unless submit is called
	 * @return int
	 */
	function getCount() {
		return $this->count;
	}

	/**
	 * @return array
	 */
	function createForm() {
		$request = $this->getRequest();
		$context = $this->getContext();
		$m = array();
		// Who to send to
		$m['spamlist'] = array(
			'id' => 'mw-massmessage-form-spamlist',
			'type' => 'text',
			'label-message' => 'massmessage-form-spamlist',
			'default' => $request->getText( 'spamlist' )
		);
		// The subject line
		$m['subject'] = array(
			'id' => 'mw-massmessage-form-subject',
			'type' => 'text',
			'label-message' => 'massmessage-form-subject',
			'default' => $request->getText( 'subject' ),
			'maxlength' => 240
		);

		// The message to send
		$m['message'] = array(
			'id' => 'mw-massmessage-form-message',
			'type' => 'textarea',
			'label-message' => 'massmessage-form-message',
			'default' => $request->getText( 'message' )
		);

		if ( $this->state == 'preview' ) {
			$m['submit-button'] = array(
				'id' => 'mw-massmessage-form-submit-button',
				'type' => 'submit',
				'default' => $context->msg( 'massmessage-form-submit' )->text()
			);
		}

		$m['preview-button'] = array(
			'id' => 'mw-massmessage-form-preview-button',
			'type' => 'submit',
			'default' => $context->msg( 'massmessage-form-preview' )->text()
		);

		return $m;
	}

	/**
	 * Log the spamming to Special:Log/massmessage
	 *
	 * @param $spamlist Title
	 * @param $subject string
	 */
	function logToWiki( $spamlist, $subject ) {
		// $title->getLatestRevID()

		$logEntry = new ManualLogEntry( 'massmessage', 'send' );
		$logEntry->setPerformer( $this->getUser() );
		$logEntry->setTarget( $spamlist );
		$logEntry->setComment( $subject );

		$logid = $logEntry->insert();
		$logEntry->publish( $logid );
	}

	/**
	 * Callback function
	 * Does some basic verification of data
	 * Decides whether to show the preview screen
	 * or the submitted message
	 *
	 * @param $data Array
	 * @return Status
	 */
	function callback( $data ) {

		$this->verifyData( $data );

		// Die on errors.
		if ( !$this->status->isOK() ) {
			$this->state = 'form';

			return $this->status;
		}

		// Add a global footer
		$footer = $this->msg( 'massmessage-message-footer' )->inContentLanguage()->parse();
		if ( trim( $footer ) ) {
			// Only add the footer if it is not just whitespace
			$data['message'] .= "\n" . $footer;
		}

		if ( $this->state == 'submit' ) {
			return $this->submit( $data );
		} else { // $this->state can only be 'preview' here
			return $this->preview( $data );
		}
	}

	/**
	 * Parse and normalize the spamlist
	 *
	 * @param $title string
	 * @return Title|string string will be a error message key
	 */
	function getSpamlist( $title ) {
		$spamlist = Title::newFromText( $title );
		if ( $spamlist === null || !$spamlist->exists() ) {
			return 'massmessage-spamlist-doesnotexist';
		} else {
			// Page exists, follow a redirect if possible
			$target = MassMessage::followRedirect( $spamlist );
			if ( $target === null || !$target->exists() ) {
				return 'massmessage-spamlist-doesnotexist'; // Interwiki redirect or non-existent page.
			} else {
				$spamlist = $target;
			}
		}

		return $spamlist;
	}

	/**
	 * Sanity check the data, throwing any errors if necessary
	 *
	 * @param $data Array
	 * @return Status
	 */
	function verifyData( $data ) {
		// Trim all the things!
		foreach ( $data as $k => $v ) {
			$data[$k] = trim( $v );
		}

		$spamlist = $this->getSpamlist( $data['spamlist'] );
		if ( !( $spamlist instanceof Title ) ) {
			$this->status->fatal( $spamlist );
		}

		if ( $data['subject'] === '' ) {
			$this->status->fatal( 'massmessage-empty-subject' );
		}

		if ( $data['message'] === '' ) {
			$this->status->fatal( 'massmessage-empty-message' );
		}

		return $this->status;
	}

	/**
	 * A preview/confirmation screen
	 *
	 * @param $data Array
	 * @return Status
	 */
	function preview( $data ) {
		// $spamlist = $this->getSpamlist( $data['spamlist'] );
		// $targets = MassMessage::getParserFunctionTargets( $spamlist, $this->getContext() );
		// $firstTarget = array_values( $targets )[0]; // Why doesn't this work??
		$firstTarget = Title::newFromText( 'Project:Example' );
		$wikipage = WikiPage::factory( $firstTarget );

		// Hacked up from EditPage.php
		// Is this really the best way to do this???

		$subject = $data['subject'];
		$message = $data['message'];

		// Convert into a content object
		$content = ContentHandler::makeContent( $message, $firstTarget );

		// Parser stuff. Taken from EditPage::getPreviewText()
		$parserOptions = $wikipage->makeParserOptions( $this->getContext() );
		$parserOptions->setEditSection( false );
		$parserOptions->setIsPreview( true );
		$parserOptions->setIsSectionPreview( false );
		$content = $content->addSectionHeader( $subject );

		// Hooks not being run: EditPageGetPreviewContent, EditPageGetPreviewText

		$content = $content->preSaveTransform( $firstTarget, MassMessage::getMessengerUser(), $parserOptions );
		$parserOutput = $content->getParserOutput( $firstTarget, null, $parserOptions );
		$previewHTML = $parserOutput->getText();
		$this->getOutput()->addWikiMsg( 'massmessage-just-preview' );
		$fieldsetMessage = $this->getContext()->msg( 'massmessage-fieldset-preview' )->text();
		$wrapFieldset = Xml::fieldset( $fieldsetMessage, $previewHTML );
		$this->getOutput()->addHTML( $wrapFieldset );

		return false;
	}

	/**
	 * Send out the message
	 *
	 * @param $data Array
	 * @return Status
	 */
	function submit( $data ) {
		global $wgDBname;
		$spamlist = $this->getSpamlist( $data['spamlist'] );

		// Prep the HTML comment message
		$data['comment'] = array(
			$this->getUser()->getName(),
			$wgDBname,
			$spamlist->getFullURL( '', false, PROTO_CANONICAL )
		);

		// Log it.
		$this->logToWiki( $spamlist, $data['subject'] );

		// Insert it into the job queue.
		$pages = MassMessage::getParserFunctionTargets( $spamlist, $this->getContext() );
		if ( !is_array( $pages ) ) {
			$this->status->fatal( $pages );
			return $this->status;
		}
		$this->count = 0;
		foreach ( $pages as $page ) {
			$title = Title::newFromText( $page['title'] );
			$job = new MassMessageJob( $title, $data );
			if ( $page['dbname'] == $wgDBname ) {
				$dbname = wfWikiID();
			} else {
				$dbname = $page['dbname'];
			}
			JobQueueGroup::singleton( $dbname )->push( $job );
			$this->count += 1;
		}

		return $this->status;
	}
}

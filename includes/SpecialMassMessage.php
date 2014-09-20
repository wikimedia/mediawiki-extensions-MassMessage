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

	public function __construct() {
		parent::__construct( 'MassMessage', 'massmessage' );
	}

	public function execute( $par ) {
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
		if ( $request->getVal( 'submit-button' ) !== null ) {
			$this->state = 'submit';
		} elseif ( $request->getVal( 'preview-button' ) !== null ) {
			$this->state = 'preview';
		} else {
			$this->state = 'form';
		}

		$form = new HtmlForm( $this->createForm(), $context );
		$form->setId( 'mw-massmessage-form' );
		$form->setDisplayFormat( 'div' );
		if ( $this->state === 'form' ) {
			$form->addPreText( $this->msg( 'massmessage-form-header' )->parse() );
		}
		$form->setWrapperLegendMsg( 'massmessage' );
		$form->suppressDefaultSubmit(); // We use our own buttons.
		$form->setSubmitCallback( array( $this, 'callback' ) );
		$form->setMethod( 'post' );

		$form->prepareForm();
		$result = $form->tryAuthorizedSubmit();
		if ( $result === true || ( $result instanceof Status && $result->isGood() ) ) {
			if ( $this->state === 'submit' ) { // If it's preview, everything is shown already.
				$msg = $this->msg( 'massmessage-submitted' )->params( $this->count )->plain();
				$output->addWikiText( $msg );
				$output->addWikiMsg( 'massmessage-nextsteps' );
			}
		} else {
			if ( $this->state === 'preview' ) {
				$result = $this->status;
			}
			$form->displayForm( $result );
		}
	}

	/**
	 * @return string
	 */
	public function getState() {
		return $this->state;
	}

	/**
	 * @return Status
	 */
	public function getStatus() {
		return $this->status;
	}

	/**
	 * Note that this won't be initalized unless submit is called
	 * @return int
	 */
	public function getCount() {
		return $this->count;
	}

	/**
	 * @return array
	 */
	protected function createForm() {
		$request = $this->getRequest();
		$m = array();
		// Who to send to
		$m['spamlist'] = array(
			'id' => 'mw-massmessage-form-spamlist',
			'name' => 'spamlist',
			'type' => 'text',
			'tabindex' => '1',
			'label-message' => 'massmessage-form-spamlist',
			'default' => $request->getText( 'spamlist' )
		);
		// The subject line
		$m['subject'] = array(
			'id' => 'mw-massmessage-form-subject',
			'name' => 'subject',
			'type' => 'text',
			'tabindex' => '2',
			'label-message' => 'massmessage-form-subject',
			'default' => $request->getText( 'subject' ),
			'maxlength' => 240
		);

		// The message to send
		$m['message'] = array(
			'id' => 'mw-massmessage-form-message',
			'name' => 'message',
			'type' => 'textarea',
			'tabindex' => '3',
			'label-message' => 'massmessage-form-message',
			'default' => $request->getText( 'message' )
		);

		if ( $this->state === 'preview' ) {
			// Adds it right before the 'Send' button
			$m['message']['help'] = EditPage::getCopyrightWarning( $this->getPageTitle( false ), 'parse' );
			$m['submit-button'] = array(
				'id' => 'mw-massmessage-form-submit-button',
				'name' => 'submit-button',
				'type' => 'submit',
				'tabindex' => '4',
				'default' => $this->msg( 'massmessage-form-submit' )->text()
			);
		}

		$m['preview-button'] = array(
			'id' => 'mw-massmessage-form-preview-button',
			'name' => 'preview-button',
			'type' => 'submit',
			'tabindex' => '5',
			'default' => $this->msg( 'massmessage-form-preview' )->text()
		);

		return $m;
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
	public function callback( array $data ) {

		MassMessage::verifyData( $data, $this->status );

		// Die on errors.
		if ( !$this->status->isOK() ) {
			$this->state = 'form';

			return $this->status;
		}

		if ( $this->state === 'submit' ) {
			$this->count = MassMessage::submit( $this->getContext(), $data );
			return $this->status;
		} else { // $this->state can only be 'preview' here
			return $this->preview( $data );
		}
	}

	/**
	 * Returns an array containing possibly unclosed HTML tags in $message
	 * TODO: Use an HTML parser instead of regular expressions
	 *
	 * @param $message string
	 * @return array
	 */
	protected function getUnclosedTags( $message ) {
		$startTags = array();
		$endTags = array();

		// For start tags, ignore ones that contain '/' (assume those are self-closing).
		preg_match_all( '|\<([\w]+)[^/]*?>|', $message, $startTags );
		preg_match_all( '|\</([\w]+)|', $message, $endTags );

		// Keep just the element names from the matched patterns.
		$startTags = $startTags[1];
		$endTags = $endTags[1];

		// Stop and return an empty array if there are no HTML tags.
		if ( empty( $startTags ) && empty( $endTags ) ) {
			return array();
		}

		// Construct a set containing elements that do not need an end tag.
		// List obtained from http://www.w3.org/TR/html-markup/syntax.html#syntax-elements
		$voidElements = array();
		$voidElementNames = array( 'area', 'base', 'br', 'col', 'command', 'embed','hr', 'img',
			'input', 'keygen', 'link', 'meta', 'param', 'source', 'track', 'wbr' );
		foreach ( $voidElementNames as $name ) {
			$voidElements[$name] = 1;
		}

		// Count start / end tags for each element, ignoring start tags of void elements.
		$tags = array();
		foreach ( $startTags as $tag ) {
			if ( !isset( $voidElements[$tag] ) ) {
				if ( !isset( $tags[$tag] ) ) {
					$tags[$tag] = 1;
				} else {
					$tags[$tag]++;
				}
			}
		}
		foreach ( $endTags as $tag ) {
			if ( !isset( $tags[$tag] ) ) {
				$tags[$tag] = -1;
			} else {
				$tags[$tag]--;
			}
		}

		$results = array();
		foreach ( $tags as $element => $num ) {
			if ( $num > 0 ) {
				$results[] = '<' . $element . '>';
			} elseif ( $num < 0 ) {
				$results[] = '</' . $element . '>';
			}
		}
		return $results;
	}

	/**
	 * A preview/confirmation screen
	 *
	 * @param $data Array
	 * @return Status
	 */
	protected function preview( array $data ) {
		$this->getOutput()->addWikiMsg( 'massmessage-just-preview' );

		// Output the number of recipients
		$spamlist = MassMessage::getSpamlist( $data['spamlist'] );
		$targets = MassMessageTargets::normalizeTargets(
			MassMessageTargets::getTargets( $spamlist, $this->getContext() )
		);
		$infoFieldset = Xml::fieldset(
			$this->msg( 'massmessage-fieldset-info' )->text(),
			$this->msg( 'massmessage-preview-count' )->numParams( count( $targets ) )->parse()
		);
		$this->getOutput()->addHTML( $infoFieldset );

		// Use a mock target as the context for rendering the preview
		$mockTarget = Title::newFromText( 'Project:Example' );
		$wikipage = WikiPage::factory( $mockTarget );

		// Hacked up from EditPage.php

		// Convert into a content object
		$content = ContentHandler::makeContent( $data['message'], $mockTarget );
		// Parser stuff. Taken from EditPage::getPreviewText()
		$parserOptions = $wikipage->makeParserOptions( $this->getContext() );
		$parserOptions->setEditSection( false );
		$parserOptions->setIsPreview( true );
		$parserOptions->setIsSectionPreview( false );
		$content = $content->addSectionHeader( $data['subject'] );

		// Hooks not being run: EditPageGetPreviewContent, EditPageGetPreviewText

		$content = $content->preSaveTransform( $mockTarget, MassMessage::getMessengerUser(), $parserOptions );
		$parserOutput = $content->getParserOutput( $mockTarget, null, $parserOptions );
		$previewHTML = $parserOutput->getText();
		$fieldsetMessage = $this->msg( 'massmessage-fieldset-preview' )->text();
		$wrapFieldset = Xml::fieldset( $fieldsetMessage, $previewHTML );
		$this->getOutput()->addHTML( $wrapFieldset );

		// Check if we have unescaped langlinks (Bug 54846)
		if ( $parserOutput->getLanguageLinks() ) {
			$this->status->fatal( 'massmessage-unescaped-langlinks' );
		}

		// Check for unclosed HTML tags (Bug 54909)
		$unclosedTags = $this->getUnclosedTags( $data['message'] );
		if ( !empty( $unclosedTags ) ) {
			$this->status->fatal(
				$this->msg( 'massmessage-badhtml' )
					->params( htmlspecialchars( $this->getLanguage()->commaList( $unclosedTags ) ) )
					->numParams( count( $unclosedTags ) )
			);
		}

		// Check for no timestamp (Bug 54848)
		if ( !preg_match( MassMessage::getTimestampRegex(), $content->getNativeData() ) ) {
			$this->status->fatal( 'massmessage-no-timestamp' );
		}

		return false;
	}
}

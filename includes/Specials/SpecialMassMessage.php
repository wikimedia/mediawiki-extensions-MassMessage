<?php

namespace MediaWiki\MassMessage\Specials;

use ContentHandler;
use EditPage;
use Html;
use HTMLForm;
use MediaWiki\MassMessage\Lookup\SpamlistLookup;
use MediaWiki\MassMessage\MassMessage;
use MediaWiki\MassMessage\MessageBuilder;
use MediaWiki\MassMessage\MessageContentFetcher\LabeledSectionContentFetcher;
use MediaWiki\MassMessage\MessageContentFetcher\LocalMessageContentFetcher;
use MediaWiki\MassMessage\PageMessage\PageMessageBuilder;
use MediaWiki\MassMessage\RequestProcessing\MassMessageRequest;
use MediaWiki\MassMessage\RequestProcessing\MassMessageRequestParser;
use MediaWiki\MediaWikiServices;
use Message;
use SpecialPage;
use Status;
use Title;
use WikiMap;
use Xml;

/**
 * Form to allow users to send messages to a lot of users at once.
 *
 * @author Kunal Mehta
 * @license GPL-2.0-or-later
 */

class SpecialMassMessage extends SpecialPage {
	/** @var Status */
	protected $status;
	/** @var string */
	protected $state;
	/** @var int */
	protected $count;
	/** @var LocalMessageContentFetcher */
	private $localMessageContentFetcher;
	/** @var LabeledSectionContentFetcher */
	private $labeledSectionContentFetcher;
	/** @var MessageBuilder */
	private $messageBuilder;
	/** @var PageMessageBuilder */
	private $pageMessageBuilder;

	public function __construct(
		LabeledSectionContentFetcher $labeledSectionContentFetcher,
		LocalMessageContentFetcher $localMessageContentFetcher,
		PageMessageBuilder $pageMessageBuilder
	) {
		parent::__construct( 'MassMessage', 'massmessage' );
		$this->labeledSectionContentFetcher = $labeledSectionContentFetcher;
		$this->localMessageContentFetcher = $localMessageContentFetcher;
		$this->messageBuilder = new MessageBuilder();
		$this->pageMessageBuilder = $pageMessageBuilder;
	}

	public function doesWrites() {
		return true;
	}

	/** @param string|null $par */
	public function execute( $par ) {
		$request = $this->getRequest();
		$context = $this->getContext();
		$output = $this->getOutput();

		$this->addHelpLink( 'Help:Extension:MassMessage' );
		$this->setHeaders();
		$this->outputHeader();
		$this->checkPermissions();

		$output->addModules( 'ext.MassMessage.special.js' );
		$output->addModuleStyles( 'ext.MassMessage.styles' );

		// Some variables...
		$this->status = new Status();

		// Figure out what state we're in.
		if ( $request->getCheck( 'submit-button' ) ) {
			$this->state = 'submit';
		} elseif ( $request->getCheck( 'preview-button' ) ) {
			$this->state = 'preview';
		} else {
			$this->state = 'form';
		}

		$form = new HTMLForm( $this->createForm(), $context );
		$form->setId( 'mw-massmessage-form' );
		$form->setDisplayFormat( 'div' );
		if ( $this->state === 'form' ) {
			$form->addPreText( $this->msg( 'massmessage-form-header' )->parse() );
		}
		$form->setWrapperLegendMsg( 'massmessage' );
		$form->suppressDefaultSubmit(); // We use our own buttons.
		$form->setSubmitCallback( [ $this, 'callback' ] );
		$form->setMethod( 'post' );

		$form->prepareForm();
		$result = $form->tryAuthorizedSubmit();
		if ( $result === true || ( $result instanceof Status && $result->isGood() ) ) {
			if ( $this->state === 'submit' ) { // If it's preview, everything is shown already.
				$output->addWikiMsg(
					'massmessage-submitted',
					Message::numParam( $this->count )
				);
				$output->addWikiMsg( 'massmessage-nextsteps' );
			}
		} else {
			if ( $this->state === 'preview' ) {
				$result = $this->status;
			}
			$form->displayForm( $result );
		}
	}

	/** @return string */
	public function getState() {
		return $this->state;
	}

	/** @return Status */
	public function getStatus() {
		return $this->status;
	}

	/**
	 * Note that this won't be initalized unless submit is called.
	 *
	 * @return int
	 */
	public function getCount() {
		return $this->count;
	}

	/** @return array */
	protected function createForm() {
		$request = $this->getRequest();
		$controlTabIndex = 1;

		$isPreview = $this->state === 'preview';

		$m = [];
		// Who to send to
		$m['spamlist'] = [
			'id' => 'mw-massmessage-form-spamlist',
			'name' => 'spamlist',
			'type' => 'text',
			'tabindex' => $controlTabIndex++,
			'label-message' => 'massmessage-form-spamlist',
			'default' => $request->getText( 'spamlist' )
		];
		// The subject line
		$m['subject'] = [
			'id' => 'mw-massmessage-form-subject',
			'name' => 'subject',
			'type' => 'text',
			'tabindex' => $controlTabIndex++,
			'label-message' => 'massmessage-form-subject',
			'default' => $request->getText( 'subject' ),
			'help-message' => 'massmessage-form-subject-help',
			'maxlength' => 240
		];

		// The page to sent as message
		$m['page-message'] = [
			'id' => 'mw-massmessage-form-page',
			'name' => 'page-message',
			'type' => 'text',
			'tabindex' => $controlTabIndex++,
			'label-message' => 'massmessage-form-page',
			'default' => $request->getText( 'page-message' ),
			'help' => $this->msg( 'massmessage-form-page-help' )->text()
		];

		$options = [ '----' => '' ];
		$pagename = $request->getText( 'page-message' );
		if ( trim( $pagename ) !== '' ) {
			$sections = $this->getLabeledSections( $pagename );
			$options += array_combine( $sections, $sections );
		}

		$m['page-subject-section'] = [
			'id' => 'mw-massmessage-form-page-subject-section',
			'name' => 'page-subject-section',
			'type' => 'select',
			'options' => $options,
			'tabindex' => $controlTabIndex++,
			'disabled' => !$isPreview,
			'label-message' => 'massmessage-form-page-subject-section',
			'default' => $request->getText( 'page-subject-section' ),
			'help-message' => 'massmessage-form-page-subject-section-help',
		];

		$m['page-message-section'] = [
			'id' => 'mw-massmessage-form-page-section',
			'name' => 'page-message-section',
			'type' => 'select',
			'options' => $options,
			'tabindex' => $controlTabIndex++,
			'disabled' => !$isPreview,
			'label-message' => 'massmessage-form-page-message-section',
			'default' => $request->getText( 'page-message-section' ),
			'help-message' => 'massmessage-form-page-message-section-help',
		];

		// The message to send
		$m['message'] = [
			'id' => 'mw-massmessage-form-message',
			'name' => 'message',
			'type' => 'textarea',
			'tabindex' => $controlTabIndex++,
			'label-message' => 'massmessage-form-message',
			'default' => $request->getText( 'message' )
		];

		if ( $isPreview ) {
			// Adds it right before the 'Send' button
			$m['message']['help'] = EditPage::getCopyrightWarning( $this->getPageTitle( false ), 'parse', $this );
			$m['submit-button'] = [
				'id' => 'mw-massmessage-form-submit-button',
				'name' => 'submit-button',
				'type' => 'submit',
				'tabindex' => $controlTabIndex++,
				'default' => $this->msg( 'massmessage-form-submit' )->text()
			];
		}

		$m['preview-button'] = [
			'id' => 'mw-massmessage-form-preview-button',
			'name' => 'preview-button',
			'type' => 'submit',
			'tabindex' => $controlTabIndex++,
			'default' => $this->msg( 'massmessage-form-preview' )->text()
		];

		return $m;
	}

	/**
	 * Callback function.
	 * Does some basic verification of data.
	 * Decides whether to show the preview screen or the submitted message.
	 *
	 * @param array $data
	 * @return Status|bool
	 */
	public function callback( array $data ) {
		$requestParser = new MassMessageRequestParser();
		$this->status = $requestParser->parseRequest( $data, $this->getUser() );

		// Die on errors.
		if ( !$this->status->isOK() ) {
			$this->state = 'form';
			return $this->status;
		}

		if ( $this->state === 'submit' ) {
			$this->count = MassMessage::submit( $this->getUser(), $this->status->getValue() );
			return $this->status;
		} else { // $this->state can only be 'preview' here
			$this->preview( $this->status->getValue() );
			return false; // No submission attempted
		}
	}

	/**
	 * Returns an array containing possibly unclosed HTML tags in $message.
	 *
	 * TODO: Use an HTML parser instead of regular expressions
	 *
	 * @param string $message
	 * @return string[]
	 */
	protected function getUnclosedTags( $message ) {
		// For start tags, ignore ones that contain '/' (assume those are self-closing).
		if ( !preg_match_all( '|\<([\w]+)[^/]*?>|', $message, $startTags ) &&
			!preg_match_all( '|\</([\w]+)|', $message, $endTags )
		) {
			return [];
		}

		// Keep just the element names from the matched patterns.
		$startTags = $startTags[1];
		$endTags = $endTags[1] ?? [];

		// Construct a set containing elements that do not need an end tag.
		// List obtained from http://www.w3.org/TR/html-markup/syntax.html#syntax-elements
		$voidElements = array_flip( [ 'area', 'base', 'br', 'col', 'command', 'embed','hr', 'img',
			'input', 'keygen', 'link', 'meta', 'param', 'source', 'track', 'wbr' ] );

		// Count start / end tags for each element, ignoring start tags of void elements.
		$tags = [];
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

		$results = [];
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
	 * A preview/confirmation screen.
	 * The preview generation code was hacked up from EditPage.php.
	 *
	 * @param MassMessageRequest $request
	 */
	protected function preview( MassMessageRequest $request ) {
		$this->getOutput()->addWikiMsg( 'massmessage-just-preview' );

		// Output the number of recipients
		$targets = SpamlistLookup::getTargets( $request->getSpamList() );
		$infoMessages = [
			$this->msg( 'massmessage-preview-count' )->numParams( count( $targets ) )->parse()
		];

		$pageMessage = null;
		$pageSubject = null;
		if ( $request->hasPageMessage() ) {
			$pageTitle = $this->localMessageContentFetcher
				->getTitle( $request->getPageMessage() )
				->getValue();

			if ( MassMessage::isSourceTranslationPage( $pageTitle ) ) {
				$infoMessages[] = $this->msg( 'massmessage-translate-page-info' )->parse();
			}

			$pageMessageBuilderResult = $this->pageMessageBuilder->getContent(
				$pageTitle,
				$request->getPageMessageSection(),
				$request->getPageSubjectSection(),
				WikiMap::getCurrentWikiId()
			);

			if ( $pageMessageBuilderResult->isOK() ) {
				$pageMessage = $pageMessageBuilderResult->getPageMessage();
				$pageSubject = $pageMessageBuilderResult->getPageSubject();
			}
		}

		$this->showPreviewInfo( $infoMessages );

		$messageText = $this->messageBuilder->buildMessage(
			$request->getMessage(),
			$pageMessage,
			// This forces language wrapping always. Good for clarity
			null,
			$request->getComment()
		);

		$subjectText = $this->messageBuilder->buildSubject(
			$request->getSubject(),
			$pageSubject,
			// This forces language wrapping always. Good for clarity
			null
		);

		// Use a mock target as the context for rendering the preview
		$mockTarget = Title::makeTitle( NS_PROJECT, 'MassMessage:A page that should not exist' );
		$services = MediaWikiServices::getInstance();
		$wikipage = $services->getWikiPageFactory()->newFromTitle( $mockTarget );

		// Convert into a content object
		$content = ContentHandler::makeContent( $messageText, $mockTarget );
		// Parser stuff. Taken from EditPage::getPreviewText()
		$parserOptions = $wikipage->makeParserOptions( $this->getContext() );
		$parserOptions->setIsPreview( true );
		$parserOptions->setIsSectionPreview( false );
		$content = $content->addSectionHeader( $subjectText );

		// Hooks not being run: EditPageGetPreviewContent, EditPageGetPreviewText
		$contentTransformer = $services->getContentTransformer();
		$content = $contentTransformer->preSaveTransform(
			$content,
			$mockTarget,
			MassMessage::getMessengerUser(),
			$parserOptions
		);
		$contentRenderer = $services->getContentRenderer();
		$parserOutput = $contentRenderer->getParserOutput( $content, $mockTarget, null, $parserOptions );
		$previewFieldset = Xml::fieldset(
			$this->msg( 'massmessage-fieldset-preview' )->text(),
			$parserOutput->getText( [ 'enableSectionEditLinks' => false ] )
		);
		$this->getOutput()->addHTML( $previewFieldset );

		$wikitextPreviewFieldset = Xml::fieldset(
			$this->msg( 'massmessage-fieldset-wikitext-preview' )->text(),
			// @phan-suppress-next-line SecurityCheck-DoubleEscaped false positive or bug
			Html::element( 'pre', [], "== {$subjectText} ==\n\n$messageText" )
		);
		$this->getOutput()->addHTML( $wikitextPreviewFieldset );

		// Check if we have unescaped langlinks (Bug 54846)
		if ( $parserOutput->getLanguageLinks() ) {
			$this->status->fatal( 'massmessage-unescaped-langlinks' );
		}

		// Check for unclosed HTML tags (Bug 54909)
		$unclosedTags = $this->getUnclosedTags( $request->getMessage() );
		if ( !empty( $unclosedTags ) ) {
			$this->status->fatal(
				$this->msg( 'massmessage-badhtml' )
					->params( $this->getLanguage()->commaList(
						array_map( 'htmlspecialchars', $unclosedTags )
					) )
					->numParams( count( $unclosedTags ) )
			);
		}

		// Check for no timestamp (Bug 54848)
		if ( !preg_match( MassMessage::getTimestampRegex(), $content->getNativeData() ) ) {
			$this->status->fatal( 'massmessage-no-timestamp' );
		}
	}

	protected function showPreviewInfo( array $infoMessages ) {
		$infoListHtml = $infoMessages[0];
		if ( count( $infoMessages ) > 1 ) {
			$infoListHtml = '<ul>';
			foreach ( $infoMessages as $info ) {
				$infoListHtml .= '<li>' . $info . '</li>';
			}
			$infoListHtml .= '</ul>';
		}

		$infoFieldset = Xml::fieldset(
			$this->msg( 'massmessage-fieldset-info' )->text(),
			$infoListHtml
		);

		$this->getOutput()->addHTML( $infoFieldset );
	}

	/**
	 * Get sections given a page name.
	 *
	 * @param string $pagename
	 * @return string[]
	 */
	private function getLabeledSections( string $pagename ): array {
		$pageTitle = Title::newFromText( $pagename );
		if ( $pageTitle ) {
			$status = $this->localMessageContentFetcher->getContent( $pageTitle );
			if ( $status->isOK() ) {
				return $this->labeledSectionContentFetcher->getSections(
					$status->getValue()->getWikitext()
				);
			}
		}

		return [];
	}
}

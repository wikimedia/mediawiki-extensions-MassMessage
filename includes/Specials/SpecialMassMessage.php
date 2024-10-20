<?php

namespace MediaWiki\MassMessage\Specials;

use MediaWiki\Content\ContentHandler;
use MediaWiki\Content\TextContent;
use MediaWiki\EditPage\EditPage;
use MediaWiki\Html\Html;
use MediaWiki\HTMLForm\HTMLForm;
use MediaWiki\MassMessage\Lookup\SpamlistLookup;
use MediaWiki\MassMessage\MassMessage;
use MediaWiki\MassMessage\MessageBuilder;
use MediaWiki\MassMessage\MessageContentFetcher\LabeledSectionContentFetcher;
use MediaWiki\MassMessage\MessageContentFetcher\LocalMessageContentFetcher;
use MediaWiki\MassMessage\PageMessage\PageMessageBuilder;
use MediaWiki\MassMessage\RequestProcessing\MassMessageRequest;
use MediaWiki\MassMessage\RequestProcessing\MassMessageRequestParser;
use MediaWiki\MediaWikiServices;
use MediaWiki\Message\Message;
use MediaWiki\Parser\Parsoid\LintErrorChecker;
use MediaWiki\SpecialPage\FormSpecialPage;
use MediaWiki\Status\Status;
use MediaWiki\Title\Title;
use MediaWiki\WikiMap\WikiMap;
use OOUI\FieldsetLayout;
use OOUI\HtmlSnippet;
use OOUI\PanelLayout;
use OOUI\Widget;

/**
 * Form to allow users to send messages to a lot of users at once.
 *
 * @author Kunal Mehta
 * @license GPL-2.0-or-later
 */

class SpecialMassMessage extends FormSpecialPage {
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

	private LintErrorChecker $lintErrorChecker;

	/**
	 * @param LabeledSectionContentFetcher $labeledSectionContentFetcher
	 * @param LocalMessageContentFetcher $localMessageContentFetcher
	 * @param PageMessageBuilder $pageMessageBuilder
	 * @param LintErrorChecker $lintErrorChecker
	 */
	public function __construct(
		LabeledSectionContentFetcher $labeledSectionContentFetcher,
		LocalMessageContentFetcher $localMessageContentFetcher,
		PageMessageBuilder $pageMessageBuilder,
		LintErrorChecker $lintErrorChecker
	) {
		parent::__construct( 'MassMessage', 'massmessage' );
		$this->labeledSectionContentFetcher = $labeledSectionContentFetcher;
		$this->localMessageContentFetcher = $localMessageContentFetcher;
		$this->messageBuilder = new MessageBuilder();
		$this->pageMessageBuilder = $pageMessageBuilder;
		$this->lintErrorChecker = $lintErrorChecker;
	}

	public function doesWrites() {
		return true;
	}

	/** @inheritDoc */
	public function execute( $par ) {
		$request = $this->getRequest();
		$output = $this->getOutput();

		$this->addHelpLink( 'Help:Extension:MassMessage' );

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

		parent::execute( $par );
	}

	/** @inheritDoc */
	protected function alterForm( HTMLForm $form ) {
		if ( $this->state === 'form' ) {
			$form->addPreHtml( $this->msg( 'massmessage-form-header' )->parse() );
		}
		return $form
			->setId( 'mw-massmessage-form' )
			->setWrapperLegendMsg( 'massmessage' )
			// We use our own buttons, so supress the default one.
			->suppressDefaultSubmit()
			->setMethod( 'post' );
	}

	/** @inheritDoc */
	protected function getDisplayFormat() {
		return 'ooui';
	}

	/** @inheritDoc */
	public function onSuccess() {
		if ( $this->state === 'submit' ) {
			$output = $this->getOutput();
			$output->addWikiMsg(
				'massmessage-submitted',
				Message::numParam( $this->count )
			);
			$output->addWikiMsg( 'massmessage-nextsteps' );
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

	/** @inheritDoc */
	protected function getFormFields() {
		$request = $this->getRequest();
		$controlTabIndex = 1;

		$isPreview = $this->state === 'preview';

		$m = [];
		// Who to send to
		$m['spamlist'] = [
			'id' => 'mw-massmessage-form-spamlist',
			'name' => 'spamlist',
			'type' => 'title',
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
			'type' => 'title',
			'tabindex' => $controlTabIndex++,
			'label-message' => 'massmessage-form-page',
			'default' => $request->getText( 'page-message' ),
			'help-message' => 'massmessage-form-page-help',
			'required' => false
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

		// If we're previewing a message and there are no errors, show the copyright warning and
		// the submit button.
		if ( $isPreview ) {
			$requestParser = new MassMessageRequestParser();
			$data = [
				'spamlist' => $request->getText( 'spamlist' ),
				'subject' => $request->getText( 'subject' ),
				'page-message' => $request->getText( 'page-message' ),
				'page-message-section' => $request->getText( 'page-message-section' ),
				'page-subject-section' => $request->getText( 'page-subject-section' ),
				'message' => $request->getText( 'message' )
			];
			$status = $requestParser->parseRequest( $data, $this->getUser() );
			if ( $status->isOK() ) {
				$m['message']['help'] = EditPage::getCopyrightWarning(
					$this->getPageTitle( false ),
					'parse',
					$this
				);
				$m['submit-button'] = [
					'id' => 'mw-massmessage-form-submit-button',
					'name' => 'submit-button',
					'type' => 'submit',
					'tabindex' => $controlTabIndex++,
					'default' => $this->msg( 'massmessage-form-submit' )->text()
				];
			}
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
	 * @inheritDoc
	 * @return Status|bool
	 */
	public function onSubmit( array $data ) {
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
		} else {
			// $this->state can only be 'preview' here
			$this->preview( $this->status->getValue() );

			// Die on errors.
			if ( !$this->status->isOK() ) {
				$this->state = 'form';
				return $this->status;
			}

			// No submission attempted
			return false;
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
		preg_match_all( '|\<([\w]+)[^/]*?>|', $message, $startTags );
		preg_match_all( '|\</([\w]+)|', $message, $endTags );

		// Keep just the element names from the matched patterns.
		$startTags = $startTags[1] ?? [];
		$endTags = $endTags[1] ?? [];

		// Stop and return an empty array if there are no HTML tags.
		if ( !$startTags && !$endTags ) {
			return [];
		}

		// Construct a set containing elements that do not need an end tag.
		// List obtained from http://www.w3.org/TR/html-markup/syntax.html#syntax-elements
		$voidElements = array_flip( [ 'area', 'base', 'br', 'col', 'command', 'embed', 'hr', 'img',
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
		$previewLayout = new PanelLayout( [
			'content' => new FieldsetLayout( [
				'label' => $this->msg( 'massmessage-fieldset-preview' )->text(),
				'items' => [
					new Widget( [
						'content' => new HtmlSnippet(
							$parserOutput->getText( [ 'enableSectionEditLinks' => false ] )
						),
					] ),
				],
			] ),
			'expanded' => false,
			'framed' => true,
			'padded' => true,
		] );
		$this->getOutput()->addHTML( $previewLayout );

		$wikitextPreviewLayout = new PanelLayout( [
			'content' => [
				new FieldsetLayout( [
					'label' => $this->msg( 'massmessage-fieldset-wikitext-preview' )->text(),
					'items' => [
						new Widget( [
							'content' => new HtmlSnippet(
								// @phan-suppress-next-next-line SecurityCheck-DoubleEscaped
								// Intentionally including escaped HTML tags in the output
								Html::element( 'pre', [], "== {$subjectText} ==\n\n$messageText" )
							),
						] ),
					],
				] ),
			],
			'expanded' => false,
			'framed' => true,
			'padded' => true,
		] );
		$this->getOutput()->addHTML( $wikitextPreviewLayout );

		// Check if we have unescaped langlinks (T56846)
		if ( $parserOutput->getLanguageLinks() ) {
			$this->status->fatal( 'massmessage-unescaped-langlinks' );
		}

		// Check for unclosed HTML tags (T56909)
		// TODO: This probably redundant with the Linter error "missing-end-tag"
		// and can be removed.
		$unclosedTags = $this->getUnclosedTags( $request->getMessage() );
		if ( $unclosedTags ) {
			$this->status->fatal(
				$this->msg( 'massmessage-badhtml' )
					->params( $this->getLanguage()->commaList(
						array_map( 'htmlspecialchars', $unclosedTags )
					) )
					->numParams( count( $unclosedTags ) )
			);
		}

		/** @var TextContent $content */
		'@phan-var TextContent $content';
		// Check for no timestamp (T56848)
		if ( !preg_match( MassMessage::getTimestampRegex(), $content->getText() ) ) {
			$this->status->fatal( 'massmessage-no-timestamp' );
		}

		// Check for Linter errors (T358818)
		$lintErrors = $this->lintErrorChecker->check( $request->getMessage() );
		foreach ( $lintErrors as $e ) {
			$msg = $this->msg( "linterror-{$e['type']}" )->parse();
			$this->status->fatal( 'massmessage-linter-error', $msg );
		}
	}

	/**
	 * @param array $infoMessages
	 */
	protected function showPreviewInfo( array $infoMessages ) {
		$infoListHtml = $infoMessages[0];
		if ( count( $infoMessages ) > 1 ) {
			$infoListHtml = '<ul>';
			foreach ( $infoMessages as $info ) {
				$infoListHtml .= '<li>' . $info . '</li>';
			}
			$infoListHtml .= '</ul>';
		}

		$infoLayout = new PanelLayout( [
			'content' => new FieldsetLayout( [
				'label' => $this->msg( 'massmessage-fieldset-info' )->text(),
				'items' => [
					new Widget( [
						'content' => new HtmlSnippet( $infoListHtml )
					] ),
				],
			] ),
			'expanded' => false,
			'framed' => true,
			'padded' => true,
		] );

		$this->getOutput()->addHTML( $infoLayout );
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

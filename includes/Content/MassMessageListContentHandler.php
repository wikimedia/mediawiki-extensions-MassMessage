<?php

namespace MediaWiki\MassMessage\Content;

use MediaWiki\Api\ApiMain;
use MediaWiki\Api\ApiUsageException;
use MediaWiki\Content\Content;
use MediaWiki\Content\ContentHandler;
use MediaWiki\Content\JsonContentHandler;
use MediaWiki\Content\Renderer\ContentParseParams;
use MediaWiki\Context\DerivativeContext;
use MediaWiki\Context\IContextSource;
use MediaWiki\Context\RequestContext;
use MediaWiki\Html\Html;
use MediaWiki\Json\FormatJson;
use MediaWiki\Language\Language;
use MediaWiki\Linker\Linker;
use MediaWiki\MainConfigNames;
use MediaWiki\MassMessage\Lookup\DatabaseLookup;
use MediaWiki\MassMessage\UrlHelper;
use MediaWiki\MediaWikiServices;
use MediaWiki\Output\OutputPage;
use MediaWiki\Parser\ParserOutput;
use MediaWiki\Request\DerivativeRequest;
use MediaWiki\Status\Status;
use MediaWiki\Title\Title;
use MediaWiki\Widget\TitleInputWidget;
use OOUI\ActionFieldLayout;
use OOUI\ButtonInputWidget;
use OOUI\ComboBoxInputWidget;
use OOUI\FieldLayout;
use OOUI\FormLayout;

class MassMessageListContentHandler extends JsonContentHandler {

	/**
	 * @param string $modelId
	 */
	public function __construct( $modelId = 'MassMessageListContent' ) {
		parent::__construct( $modelId );
	}

	/**
	 * @return MassMessageListContent
	 */
	public function makeEmptyContent() {
		return new MassMessageListContent( '{"description":"","targets":[]}' );
	}

	/**
	 * @return string
	 */
	protected function getContentClass() {
		return MassMessageListContent::class;
	}

	/**
	 * @param IContextSource $context
	 * @param array $options
	 * @return MassMessageListSlotDiffRenderer
	 */
	public function getSlotDiffRendererWithOptions( IContextSource $context, $options = [] ) {
		return new MassMessageListSlotDiffRenderer(
			$this->createTextSlotDiffRenderer( $options ),
			$context
		);
	}

	/**
	 * @return bool
	 */
	public function isParserCacheSupported() {
		return true;
	}

	/**
	 * Edit a delivery list via the edit API
	 * @param Title $title
	 * @param string $description
	 * @param array $targets
	 * @param string $summary Message key for edit summary
	 * @param bool $isMinor Is this a minor edit
	 * @param string $watchlist Value to pass to the edit API for the watchlist parameter.
	 * @param IContextSource $context The calling context
	 * @return Status
	 */
	public static function edit(
		Title $title, $description, $targets, $summary, $isMinor, $watchlist, IContextSource $context
	) {
		$jsonText = FormatJson::encode(
			[ 'description' => $description, 'targets' => $targets ]
		);
		if ( $jsonText === null ) {
			return Status::newFatal( 'massmessage-ch-tojsonerror' );
		}

		// Ensure that a valid context is provided to the API in unit tests
		$der = new DerivativeContext( $context );
		$requestParameters = [
			'action' => 'edit',
			'title' => $title->getFullText(),
			'contentmodel' => 'MassMessageListContent',
			'text' => $jsonText,
			'watchlist' => $watchlist,
			'summary' => $summary,
			'token' => $context->getUser()->getEditToken(),
		];
		if ( $isMinor ) {
			$requestParameters['minor'] = $isMinor;
		}
		$request = new DerivativeRequest(
			$context->getRequest(),
			$requestParameters,
			// Treat data as POSTed
			true
		);
		$der->setRequest( $request );

		try {
			$api = new ApiMain( $der, true );
			$api->execute();
		} catch ( ApiUsageException $e ) {
			return Status::wrap( $e->getStatusValue() );
		}
		return Status::newGood();
	}

	/**
	 * Deduplicate and sort a target array
	 * @param array[] $targets
	 * @return array[]
	 */
	public static function normalizeTargetArray( $targets ) {
		$targets = array_unique( $targets, SORT_REGULAR );
		usort( $targets, [ __CLASS__, 'compareTargets' ] );
		return $targets;
	}

	/**
	 * Compare two targets for ordering
	 * @param array $a
	 * @param array $b
	 * @return int
	 */
	public static function compareTargets( $a, $b ) {
		if ( !array_key_exists( 'site', $a ) && array_key_exists( 'site', $b ) ) {
			return -1;
		} elseif ( array_key_exists( 'site', $a ) && !array_key_exists( 'site', $b ) ) {
			return 1;
		} elseif ( array_key_exists( 'site', $a ) && array_key_exists( 'site', $b )
			&& $a['site'] !== $b['site']
		) {
			return strcmp( $a['site'], $b['site'] );
		} else {
			return strcmp( $a['title'], $b['title'] );
		}
	}

	/**
	 * Helper function to extract and validate title and site (if specified) from a target string
	 * @param string $target
	 * @return array Contains an 'errors' key for an array of errors if the string is invalid
	 */
	public static function extractTarget( $target ) {
		$config = MediaWikiServices::getInstance()->getMainConfig();

		$target = trim( $target );
		$delimiterPos = strrpos( $target, '@' );
		if ( $delimiterPos !== false && $delimiterPos < strlen( $target ) ) {
			$titleText = substr( $target, 0, $delimiterPos );
			$site = strtolower( substr( $target, $delimiterPos + 1 ) );
		} else {
			$titleText = $target;
			$site = null;
		}

		$result = [];

		$title = Title::newFromText( $titleText );
		if ( !$title
			|| $title->getText() === ''
			|| !$title->canExist()
		) {
			$result['errors'][] = 'invalidtitle';
		} else {
			// Use the canonical form.
			$result['title'] = $title->getPrefixedText();
		}

		if ( $site !== null && $site !== UrlHelper::getBaseUrl( $config->get( MainConfigNames::CanonicalServer ) ) ) {
			if ( !$config->get( 'AllowGlobalMessaging' ) || DatabaseLookup::getDBName( $site ) === null ) {
				$result['errors'][] = 'invalidsite';
			} else {
				$result['site'] = $site;
			}
		} elseif ( $title && $title->isExternal() ) {
			// Target has site set to current wiki, but external title
			// TODO: Provide better error message?
			$result['errors'][] = 'invalidtitle';
		}

		return $result;
	}

	/**
	 * @param Title $title
	 * @param Content|null $content
	 * @return Language
	 */
	public function getPageLanguage( Title $title, Content $content = null ) {
		// This class inherits from JsonContentHandler, which hardcodes English.
		// Use the default method from ContentHandler instead to get the page/site language.
		return ContentHandler::getPageLanguage( $title, $content );
	}

	/**
	 * @param Title $title
	 * @param Content|null $content
	 * @return Language
	 */
	public function getPageViewLanguage( Title $title, Content $content = null ) {
		// Most of the interface is rendered in user language
		return RequestContext::getMain()->getLanguage();
	}

	/**
	 * @inheritDoc
	 */
	protected function fillParserOutput(
		Content $content,
		ContentParseParams $cpoParams,
		ParserOutput &$output
	) {
		'@phan-var MassMessageListContent $content';
		$services = MediaWikiServices::getInstance();

		$page = $cpoParams->getPage();
		$revId = $cpoParams->getRevId();
		$parserOptions = $cpoParams->getParserOptions();
		// Parse the description text.
		$output = $services->getParser()
			->parse( $content->getDescription(), $page, $parserOptions, true, true, $revId );
		$services->getTrackingCategories()->addTrackingCategory( $output, 'massmessage-list-category', $page );
		$lang = $parserOptions->getUserLangObj();

		if ( $content->hasInvalidTargets() ) {
			$warning = Html::element( 'p', [ 'class' => 'error' ],
				wfMessage( 'massmessage-content-invalidtargets' )->inLanguage( $lang )->text()
			);
		} else {
			$warning = '';
		}

		// Mark the description language (may be different from user language used to render the rest of the page)
		$description = $output->getRawText();
		$title = Title::castFromPageReference( $page );
		$pageLang = $title->getPageLanguage();
		$attribs = [ 'lang' => $pageLang->getHtmlCode(), 'dir' => $pageLang->getDir() ];

		$output->setEnableOOUI( true );
		OutputPage::setupOOUI();
		$output->setText( $warning . Html::rawElement( 'div', $attribs, $description ) . self::getAddForm( $lang )
			. $this->getTargetsHtml( $content, $lang ) );

		// Update the links table.
		$targets = $content->getTargets();
		foreach ( $targets as $target ) {
			if ( !array_key_exists( 'site', $target ) ) {
				$output->addLink( Title::newFromText( $target['title'] ) );
			} else {
				$output->addExternalLink(
					'//' . $target['site'] . $services->getMainConfig()->get( MainConfigNames::Script )
					. '?title=' . Title::newFromText( $target['title'] )->getPrefixedURL() );
			}
		}

		$output->addModuleStyles( [ 'ext.MassMessage.styles' ] );
		$output->addModules( [ 'ext.MassMessage.content' ] );
	}

	/**
	 * Helper function for fillParserOutput; return HTML for displaying the list of pages.
	 * Note that the function assumes that the contents are valid.
	 *
	 * @param MassMessageListContent $content
	 * @param Language $lang
	 * @return string
	 */
	private function getTargetsHtml( MassMessageListContent $content, Language $lang ) {
		$services = MediaWikiServices::getInstance();

		$html = Html::element( 'h2', [],
			wfMessage( 'massmessage-content-pages' )->inLanguage( $lang )->text() );

		$sites = $this->getTargetsBySite( $content );

		// If the list is empty
		if ( count( $sites ) === 0 ) {
			$html .= Html::element( 'p', [],
				wfMessage( 'massmessage-content-empty' )->inLanguage( $lang )->text() );
			return $html;
		}

		// Use LinkBatch to cache existence for all local targets for later use by Linker.
		if ( array_key_exists( 'local', $sites ) ) {
			$lb = $services->getLinkBatchFactory()->newLinkBatch();
			foreach ( $sites['local'] as $target ) {
				$lb->addObj( Title::newFromText( $target ) );
			}
			$lb->execute();
		}

		// Determine whether there are targets on external wikis.
		$printSites = count( $sites ) !== 1 || !array_key_exists( 'local', $sites );
		$linkRenderer = $services->getLinkRenderer();
		foreach ( $sites as $site => $targets ) {
			if ( $printSites ) {
				if ( $site === 'local' ) {
					$html .= Html::element( 'p', [],
						wfMessage( 'massmessage-content-localpages' )->inLanguage( $lang )->text()
					);
				} else {
					$html .= Html::element( 'p', [],
						wfMessage( 'massmessage-content-pagesonsite', $site )->inLanguage( $lang )
						->text()
					);
				}
			}

			$html .= Html::openElement( 'ul' );
			foreach ( $targets as $target ) {
				$title = Title::newFromText( $target );

				// Generate the HTML for the link to the target.
				if ( $site === 'local' ) {
					$targetLink = $linkRenderer->makeLink( $title );
				} else {
					$script = $services->getMainConfig()->get( MainConfigNames::Script );
					$targetLink = Linker::makeExternalLink(
						"//$site$script?title=" . $title->getPrefixedURL(),
						$title->getPrefixedText()
					);
				}

				// Generate the HTML for the remove link.
				$removeLink = Html::element( 'a',
					[
						'data-title' => $title->getPrefixedText(),
						'data-site' => $site,
						'href' => '#',
					],
					wfMessage( 'massmessage-content-remove' )->inLanguage( $lang )->text()
				);

				$html .= Html::openElement( 'li' );
				$html .= Html::rawElement( 'span', [ 'class' => 'mw-massmessage-targetlink' ],
					$targetLink );
				$html .= Html::rawElement( 'span', [ 'class' => 'mw-massmessage-removelink' ],
					'(' . $removeLink . ')' );
				$html .= Html::closeElement( 'li' );
			}
			$html .= Html::closeElement( 'ul' );
		}

		return $html;
	}

	/**
	 * Helper function for getTargetsHtml; return the array of targets sorted by site.
	 * Note that the function assumes that the contents are valid.
	 *
	 * @param MassMessageListContent $content
	 * @return array
	 */
	private function getTargetsBySite( MassMessageListContent $content ) {
		$targets = $content->getTargets();
		$results = [];
		foreach ( $targets as $target ) {
			if ( array_key_exists( 'site', $target ) ) {
				$results[$target['site']][] = $target['title'];
			} else {
				$results['local'][] = $target['title'];
			}
		}
		return $results;
	}

	/**
	 * Helper function for fillParserOutput; return HTML for page-adding form and
	 * (initially empty and hidden) list of added pages.
	 *
	 * @param Language $lang
	 * @return string
	 */
	private static function getAddForm( Language $lang ) {
		$config = MediaWikiServices::getInstance()->getMainConfig();

		$html = Html::openElement( 'div', [ 'id' => 'mw-massmessage-addpages' ] );
		$html .= Html::element( 'h2', [],
			wfMessage( 'massmessage-content-addheading' )->inLanguage( $lang )->text() );

		$titleWidget = new TitleInputWidget( [] );
		$titleLabel = wfMessage( 'massmessage-content-addtitle' )->inLanguage( $lang )->text();
		$submitWidget = new ButtonInputWidget( [
			'type' => 'submit',
			'label' => wfMessage( 'massmessage-content-addsubmit' )->inLanguage( $lang )->text(),
		] );
		$sites = DatabaseLookup::getDatabases();
		if ( $config->get( 'AllowGlobalMessaging' ) && count( $sites ) > 1 ) {
			// Treat all 3 widgets as distinct items in the layout
			$items = [
				new FieldLayout(
					$titleWidget,
					[
						'id' => 'mw-massmessage-addtitle',
						'label' => $titleLabel,
						'align' => 'top',
					],
				),
				new FieldLayout(
					new ComboBoxInputWidget( [
						'name' => 'site',
						'placeholder' => UrlHelper::getBaseUrl( $config->get( MainConfigNames::CanonicalServer ) ),
						'autocomplete' => true,
						'options' => array_map(
							static function ( $domain ) {
								return [ 'data' => $domain, 'label' => $domain ];
							},
							array_keys( $sites )
						),
					] ),
					[
						'id' => 'mw-massmessage-addsite',
						'label' => wfMessage( 'massmessage-content-addsite' )->inLanguage( $lang )->text(),
						'align' => 'top',
					]
				),
				new FieldLayout( $submitWidget )
			];
		} else {
			// Use a joined layout
			$items = [
				new ActionFieldLayout(
					$titleWidget,
					$submitWidget,
					[
						'id' => 'mw-massmessage-addtitle',
						'label' => $titleLabel,
						'align' => 'top',
					]
				)
			];
		}
		$html .= new FormLayout( [
			'id' => 'mw-massmessage-addform',
			'items' => $items,
			'infusable' => true,
		] );

		// List of added pages
		$html .= Html::rawElement(
			'div',
			[ 'id' => 'mw-massmessage-addedlist' ],
			Html::element( 'p', [], wfMessage( 'massmessage-content-addedlistheading' )->inLanguage( $lang )->text() ) .
				Html::element( 'ul', [], '' )
		);

		$html .= Html::closeElement( 'div' );
		return $html;
	}
}

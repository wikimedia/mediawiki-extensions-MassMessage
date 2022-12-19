<?php

namespace MediaWiki\MassMessage\Content;

use ApiMain;
use ApiUsageException;
use Content;
use ContentHandler;
use DerivativeContext;
use DerivativeRequest;
use FormatJson;
use Html;
use IContextSource;
use JsonContentHandler;
use Language;
use Linker;
use MediaWiki\Content\Renderer\ContentParseParams;
use MediaWiki\MassMessage\Lookup\DatabaseLookup;
use MediaWiki\MassMessage\UrlHelper;
use MediaWiki\MediaWikiServices;
use ParserOutput;
use RequestContext;
use Status;
use Title;

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
	 * @return string
	 */
	protected function getDiffEngineClass() {
		return MassMessageListDiffEngine::class;
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
	 * @param IContextSource $context The calling context
	 * @return Status
	 */
	public static function edit( Title $title, $description, $targets, $summary,
		IContextSource $context
	) {
		$jsonText = FormatJson::encode(
			[ 'description' => $description, 'targets' => $targets ]
		);
		if ( $jsonText === null ) {
			return Status::newFatal( 'massmessage-ch-tojsonerror' );
		}

		// Ensure that a valid context is provided to the API in unit tests
		$der = new DerivativeContext( $context );
		$request = new DerivativeRequest(
			$context->getRequest(),
			[
				'action' => 'edit',
				'title' => $title->getFullText(),
				'contentmodel' => 'MassMessageListContent',
				'text' => $jsonText,
				'summary' => $summary,
				'token' => $context->getUser()->getEditToken(),
			],
			true // Treat data as POSTed
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
		global $wgCanonicalServer, $wgAllowGlobalMessaging;

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
			$result['title'] = $title->getPrefixedText(); // Use the canonical form.
		}

		if ( $site !== null && $site !== UrlHelper::getBaseUrl( $wgCanonicalServer ) ) {
			if ( !$wgAllowGlobalMessaging || DatabaseLookup::getDBName( $site ) === null ) {
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
		global $wgScript;

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
		$attribs = [ 'lang' => $pageLang->getHtmlCode(), 'dir' => $pageLang->getDir(),
			'class' => 'mw-content-' . $pageLang->getDir() ];

		$output->setText( $warning . Html::rawElement( 'div', $attribs, $description ) . self::getAddForm( $lang )
			. $this->getTargetsHtml( $content, $lang ) );

		// Update the links table.
		$targets = $content->getTargets();
		foreach ( $targets as $target ) {
			if ( !array_key_exists( 'site', $target ) ) {
				$output->addLink( Title::newFromText( $target['title'] ) );
			} else {
				$output->addExternalLink( '//' . $target['site'] . $wgScript . '?title='
					. Title::newFromText( $target['title'] )->getPrefixedURL() );
			}
		}
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
		global $wgScript;

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
			$lb = MediaWikiServices::getInstance()->getLinkBatchFactory()->newLinkBatch();
			foreach ( $sites['local'] as $target ) {
				$lb->addObj( Title::newFromText( $target ) );
			}
			$lb->execute();
		}

		// Determine whether there are targets on external wikis.
		$printSites = count( $sites ) !== 1 || !array_key_exists( 'local', $sites );
		$linkRenderer = MediaWikiServices::getInstance()->getLinkRenderer();
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
					$targetLink = Linker::makeExternalLink(
						"//$site$wgScript?title=" . $title->getPrefixedURL(),
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
		global $wgAllowGlobalMessaging, $wgCanonicalServer;

		$html = Html::openElement( 'div', [ 'id' => 'mw-massmessage-addpages' ] );
		$html .= Html::element( 'h2', [],
			wfMessage( 'massmessage-content-addheading' )->inLanguage( $lang )->text() );

		$html .= Html::openElement( 'form', [ 'id' => 'mw-massmessage-addform' ] );
		$html .= Html::element( 'label', [ 'for' => 'mw-massmessage-addtitle' ],
			wfMessage( 'massmessage-content-addtitle' )->inLanguage( $lang )->text() );
		$html .= Html::input( 'title', '', 'text', [ 'id' => 'mw-massmessage-addtitle' ] );
		if ( $wgAllowGlobalMessaging && count( DatabaseLookup::getDatabases() ) > 1 ) {
			$html .= Html::element( 'label', [ 'for' => 'mw-massmessage-addsite' ],
				wfMessage( 'massmessage-content-addsite' )->inLanguage( $lang )->text() );
			$html .= Html::input( 'site', '', 'text', [
				'id' => 'mw-massmessage-addsite',
				'placeholder' => UrlHelper::getBaseUrl( $wgCanonicalServer )
			] );
		}
		$html .= Html::input( 'submit',
			wfMessage( 'massmessage-content-addsubmit' )->inLanguage( $lang )->text(),
			'submit', [ 'id' => 'mw-massmessage-addsubmit' ] );
		$html .= Html::closeElement( 'form' );

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

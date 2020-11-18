<?php

namespace MediaWiki\MassMessage\Content;

use Html;
use JsonContent;
use Language;
use LinkBatch;
use Linker;
use MediaWiki\MassMessage\DatabaseLookup;
use MediaWiki\MassMessage\UrlHelper;
use MediaWiki\MediaWikiServices;
use ParserOptions;
use ParserOutput;
use Title;

class MassMessageListContent extends JsonContent {

	/**
	 * Description wikitext.
	 *
	 * @var string|null
	 */
	protected $description;

	/**
	 * Array of target pages.
	 *
	 * @var array[]|null
	 */
	protected $targets;

	/**
	 * Whether $description and $targets have been populated.
	 *
	 * @var bool
	 */
	protected $decoded = false;

	/**
	 * @param string $text
	 */
	public function __construct( $text ) {
		parent::__construct( $text, 'MassMessageListContent' );
	}

	/**
	 * Decode and validate the contents.
	 *
	 * @return bool Whether the contents are valid
	 */
	public function isValid() {
		$this->decode(); // Populate $this->description and $this->targets.
		if ( !is_string( $this->description ) || !is_array( $this->targets ) ) {
			return false;
		}
		foreach ( $this->getTargets() as $target ) {
			if ( !is_array( $target )
				|| !array_key_exists( 'title', $target )
				|| Title::newFromText( $target['title'] ) === null
			) {
				return false;
			}
		}
		return true;
	}

	/**
	 * Whether the content object contains invalid targets.
	 *
	 * @return bool
	 */
	public function hasInvalidTargets() {
		return count( $this->getTargets() ) !== count( $this->getValidTargets() );
	}

	/**
	 * @return string|null
	 */
	public function getDescription() {
		$this->decode();
		return $this->description;
	}

	/**
	 * @return array[]
	 */
	public function getTargets() {
		$this->decode();
		if ( is_array( $this->targets ) ) {
			return $this->targets;
		}
		return [];
	}

	/**
	 * Return only the targets that would be valid for delivery.
	 *
	 * @return array
	 */
	public function getValidTargets() {
		global $wgAllowGlobalMessaging;

		$targets = $this->getTargets();
		$validTargets = [];
		foreach ( $targets as $target ) {
			if ( !array_key_exists( 'site', $target )
				|| $wgAllowGlobalMessaging
				&& DatabaseLookup::getDBName( $target['site'] ) !== null
			) {
				$validTargets[] = $target;
			}
		}
		return $validTargets;
	}

	/**
	 * Return targets as an array of title or title@site strings.
	 *
	 * @return array
	 */
	public function getTargetStrings() {
		global $wgCanonicalServer;

		$targets = $this->getTargets();
		$targetStrings = [];
		foreach ( $targets as $target ) {
			if ( array_key_exists( 'site', $target ) ) {
				$targetStrings[] = $target['title'] . '@' . $target['site'];
			} elseif ( strpos( $target['title'], '@' ) !== false ) {
				// List the site if it'd otherwise be ambiguous
				$targetStrings[] = $target['title'] . '@'
					. UrlHelper::getBaseUrl( $wgCanonicalServer );
			} else {
				$targetStrings[] = $target['title'];
			}
		}
		return $targetStrings;
	}

	/**
	 * Decode the JSON contents and populate $description and $targets.
	 */
	protected function decode() {
		if ( $this->decoded ) {
			return;
		}
		$jsonParse = $this->getData();
		$data = $jsonParse->isGood() ? $jsonParse->getValue() : null;
		if ( $data ) {
			$this->description = $data->description ?? null;
			if ( isset( $data->targets ) && is_array( $data->targets ) ) {
				$this->targets = [];
				foreach ( $data->targets as $target ) {
					if ( !is_object( $target ) ) { // There is a malformed target.
						$this->targets = null;
						break;
					}
					$this->targets[] = wfObjectToArray( $target ); // Convert to associative array.
				}
			} else {
				$this->targets = null;
			}
		}
		$this->decoded = true;
	}

	/**
	 * Fill $output with information derived from the content.
	 *
	 * @param Title $title
	 * @param int $revId
	 * @param ParserOptions $options
	 * @param bool $generateHtml
	 * @param ParserOutput &$output
	 */
	protected function fillParserOutput( Title $title, $revId, ParserOptions $options,
		$generateHtml, ParserOutput &$output
	) {
		global $wgScript;

		// Parse the description text.
		$output = MediaWikiServices::getInstance()->getParser()
			->parse( $this->getDescription(), $title, $options, true, true, $revId );
		$output->addTrackingCategory( 'massmessage-list-category', $title );
		$lang = $options->getUserLangObj();

		// Generate output HTML, if needed.
		if ( $generateHtml ) {
			if ( $this->hasInvalidTargets() ) {
				$warning = Html::element( 'p', [ 'class' => 'error' ],
					wfMessage( 'massmessage-content-invalidtargets' )->inLanguage( $lang )->text()
				);
			} else {
				$warning = '';
			}

			// Mark the description language (may be different from user language used to render the rest of the page)
			$description = $output->getRawText();
			$pageLang = $title->getPageLanguage();
			$attribs = [ 'lang' => $pageLang->getHtmlCode(), 'dir' => $pageLang->getDir(),
				'class' => 'mw-content-' . $pageLang->getDir() ];

			$output->setText( $warning . Html::rawElement( 'div', $attribs, $description ) . self::getAddForm( $lang )
				. $this->getTargetsHtml( $lang ) );
		} else {
			$output->setText( '' );
		}

		// Update the links table.
		$targets = $this->getTargets();
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
	 * @param Language $lang
	 * @return string
	 */
	protected function getTargetsHtml( Language $lang ) {
		global $wgScript;

		$html = Html::element( 'h2', [],
			wfMessage( 'massmessage-content-pages' )->inLanguage( $lang )->text() );

		$sites = $this->getTargetsBySite();

		// If the list is empty
		if ( count( $sites ) === 0 ) {
			$html .= Html::element( 'p', [],
				wfMessage( 'massmessage-content-empty' )->inLanguage( $lang )->text() );
			return $html;
		}

		// Use LinkBatch to cache existence for all local targets for later use by Linker.
		if ( array_key_exists( 'local', $sites ) ) {
			$lb = new LinkBatch;
			foreach ( $sites['local'] as $target ) {
				$lb->addObj( Title::newFromText( $target ) );
			}
			$lb->execute();
		}

		// Determine whether there are targets on external wikis.
		$printSites = ( count( $sites ) === 1 && array_key_exists( 'local', $sites ) ) ?
			false : true;

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
					$targetLink = Linker::link( $title );
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
	 * @return array
	 */
	protected function getTargetsBySite() {
		$targets = $this->getTargets();
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
	 protected static function getAddForm( Language $lang ) {
		global $wgAllowGlobalMessaging, $wgCanonicalServer;

		$html = Html::openElement( 'div', [ 'id' => 'mw-massmessage-addpages' ] );
		$html .= Html::element( 'h2', [],
			wfMessage( 'massmessage-content-addheading' )->inLanguage( $lang )->text() );

		// Form
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
		$html .= Html::openElement( 'div', [ 'id' => 'mw-massmessage-addedlist' ] );
		$html .= Html::element( 'p', [],
			wfMessage( 'massmessage-content-addedlistheading' )->inLanguage( $lang )->text() );
		$html .= Html::element( 'ul', [], '' );
		$html .= Html::closeElement( 'div' );

		$html .= Html::closeElement( 'div' );
		return $html;
	 }
}

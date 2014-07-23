<?php

class MassMessageListContent extends TextContent {

	/**
	 * Description wikitext
	 * @var string|null
	 */
	protected $description;


	/**
	 * Array of target pages
	 * @var array|null
	 */
	protected $targets;

	/**
	 * Whether $description and $targets have been populated
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
		global $wgAllowGlobalMessaging;

		$this->decode(); // Populate $this->description and $this->targets.
		if ( !is_string( $this->description ) || !is_array( $this->targets ) ) {
			return false;
		}
		foreach ( $this->targets as $target ) {
			if ( !is_array( $target )
				|| !array_key_exists( 'title', $target )
				|| Title::newFromText( $target['title'] ) === null
			) {
				return false;
			}
			if ( array_key_exists( 'site', $target ) ) {
				$wiki = MassMessage::getDBName( $target['site'] );
				if ( $wiki === null || !$wgAllowGlobalMessaging && $wiki !== wfWikiId() ) {
					return false;
				}
			}
		}
		return true;
	}

	/**
	 * @return string|null
	 */
	public function getDescription() {
		$this->decode();
		return $this->description;
	}

	/**
	 * @return array|null
	 */
	public function getTargets() {
		$this->decode();
		return $this->targets;
	}

	/**
	 * Decode the JSON contents and populate $description and $targets.
	 */
	protected function decode() {
		if ( $this->decoded ) {
			return;
		}
		$data = FormatJson::decode( $this->getNativeData(), true );
		if ( is_array( $data ) ) {
			$this->description = array_key_exists( 'description', $data ) ?
				$data['description'] : null;
			$this->targets = array_key_exists( 'targets', $data ) ? $data['targets'] : null;
		}
		$this->decoded = true;
	}

	/**
	 * Fill $output with information derived from the content.
	 * @param Title $title
	 * @param int $revId
	 * @param ParserOptions $options
	 * @param bool $generateHtml
	 * @param ParserOutput $output
	 */
	protected function fillParserOutput( Title $title, $revId, ParserOptions $options,
		$generateHtml, ParserOutput &$output
	) {
		global $wgParser, $wgScript;

		// Parse the description text.
		$output = $wgParser->parse( $this->getDescription(), $title, $options, true, true, $revId );

		// Generate output HTML, if needed.
		if ( $generateHtml ) {
			$output->setText( $output->getText() . $this->getTargetsHtml() . self::getAddForm() );
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
	 * @return string
	 */
	protected function getTargetsHtml() {
		global $wgScript;

		$html = Html::element( 'h2', array(), wfMessage( 'massmessage-content-pages' )->text() );

		$sites = $this->getTargetsBySite();

		// If the list is empty
		if ( count( $sites ) === 0 ) {
			$html .= Html::element( 'p', array(),
				wfMessage( 'massmessage-content-empty' )->text() );
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
					$html .= Html::element( 'p', array(),
						wfMessage( 'massmessage-content-localpages' )->text() );
				} else {
					$html .= Html::element( 'p', array(),
						wfMessage( 'massmessage-content-pagesonsite', $site )->text() );
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
					array(
						'data-title' => $title->getPrefixedText(),
						'data-site' => $site,
						'href' => '#',
					),
					wfMessage( 'massmessage-content-remove' )->text()
				);

				$html .= Html::openElement( 'li' );
				$html .= Html::rawElement( 'span', array( 'class' => 'mw-massmessage-targetlink' ),
					$targetLink );
				$html .= Html::rawElement( 'span', array( 'class' => 'mw-massmessage-removelink' ),
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
	 * @return array
	 */
	protected function getTargetsBySite() {
		$targets = $this->getTargets();
		$results = array();
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
	 * @return string
	 */
	 protected static function getAddForm() {
		global $wgAllowGlobalMessaging, $wgCanonicalServer;

		$html = Html::openElement( 'div', array( 'id' => 'mw-massmessage-addpages' ) );
		$html .= Html::element( 'h2', array(),
			wfMessage( 'massmessage-content-addheading' )->text() );

		// Form
		$html .= Html::openElement( 'form', array( 'id' => 'mw-massmessage-addform' ) );
		$html .= Html::element( 'label', array( 'for' => 'mw-massmessage-addtitle' ),
			wfMessage( 'massmessage-content-addtitle' )->text() );
		$html .= Html::input( 'title', '', 'text', array( 'id' => 'mw-massmessage-addtitle' ) );
		if ( $wgAllowGlobalMessaging ) {
			$html .= Html::element( 'label', array( 'for' => 'mw-massmessage-addsite' ),
				wfMessage( 'massmessage-content-addsite' )->text() );
			$html .= Html::input( 'site', '', 'text', array(
				'id' => 'mw-massmessage-addsite',
				'placeholder' => MassMessage::getBaseUrl( $wgCanonicalServer )
			) );
		}
		$html .= Html::input( 'submit', wfMessage( 'massmessage-content-addsubmit' )->escaped(),
			'submit', array( 'id' => 'mw-massmessage-addsubmit' ) );
		$html .= Html::closeElement( 'form' );

		// List of added pages
		$html .= Html::openElement( 'div', array( 'id' => 'mw-massmessage-addedlist' ) );
		$html .= Html::element( 'p', array(),
			wfMessage( 'massmessage-content-addedlistheading' )->text() );
		$html .= Html::element( 'ul', array(), '' );
		$html .= Html::closeElement( 'div' );

		$html .= Html::closeElement( 'div' );
		return $html;
	 }
}

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

		// Add the list content to the output, if needed.
		if ( $generateHtml ) {
			$output->setText( $output->getText() . $this->getTargetsHtml() );
		} else {
			$output->setText( '' );
		}
	}

	/**
	 * Helper function for fillParserOutput; return HTML for displaying the list of pages.
	 * Note that the function assumes that the contents are valid.
	 * @return string
	 */
	protected function getTargetsHtml() {
		global $wgScript;

		$html = '<h2>' . wfMessage( 'massmessage-content-pages' )->parse() . "</h2>\n";

		$sites = $this->getTargetsBySite();

		// If the list is empty
		if ( count( $sites ) === 0 ) {
			$html .= '<p>' . wfMessage( 'massmessage-content-empty' )->parse() . "</p>\n";
			return $html;
		}

		// Determine whether there are targets on external wikis.
		$printSites = ( count( $sites ) === 1 && array_key_exists( 'local', $sites ) ) ?
			false : true;

		foreach ( $sites as $site => $targets ) {
			if ( $printSites ) {
				if ( $site === 'local' ) {
					$html .= '<p>' . wfMessage( 'massmessage-content-localpages' )->parse()
						. "</p>\n";
				} else {
					$html .= '<p>'
						. wfMessage( 'massmessage-content-pagesonsite', $site )->parse()
						. "</p>\n";
				}
			}

			$html .= "<ul>\n";
			foreach ( $targets as $target ) {
				if ( $site === 'local' ) {
					$html .= '<li>' . Linker::link( Title::newFromText( $target ) ) . "</li>\n";
				} else {
					$title = Title::newFromText( $target );
					$url = "//$site$wgScript?title=" . $title->getPrefixedURL();
					$html .= '<li>'
						. Linker::makeExternalLink( $url, $title->getPrefixedText() )
						. "</li>\n";
				}
			}
			$html .= "</ul>\n";
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
}

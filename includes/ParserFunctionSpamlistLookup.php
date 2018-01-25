<?php

namespace MediaWiki\MassMessage;

use Title;
use Parser;
use WikiPage;
use Revision;

class ParserFunctionSpamlistLookup extends SpamlistLookup {

	/**
	 * @var Title
	 */
	protected $spamlist;

	public function __construct( Title $spamlist ) {
		$this->spamlist = $spamlist;
	}

	/**
	 * Get an array of targets via the #target parser function
	 * @return array
	 */
	public function fetchTargets() {
		$page = WikiPage::factory( $this->spamlist );
		$text = $page->getContent( Revision::RAW )->getNativeData();

		// Prep the parser
		$parserOptions = $page->makeParserOptions( 'canonical' );
		$parser = new Parser();
		$parser->firstCallInit(); // So our initial parser function is added
		// Now overwrite it
		$parser->setFunctionHook(
			'target',
			'MediaWiki\\MassMessage\\MassMessageHooks::storeDataParserFunction'
		);

		// Parse
		$output = $parser->parse( $text, $this->spamlist, $parserOptions );
		$data = $output->getExtensionData( 'massmessage-targets' );

		if ( $data ) {
			return $data;
		} else {
			return []; // No parser functions on page
		}
	}
}

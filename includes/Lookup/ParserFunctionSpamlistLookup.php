<?php

namespace MediaWiki\MassMessage\Lookup;

use MediaWiki\MassMessage\MassMessageHooks;
use MediaWiki\MediaWikiServices;
use MediaWiki\Revision\RevisionRecord;
use Title;
use WikiPage;

class ParserFunctionSpamlistLookup extends SpamlistLookup {

	/**
	 * @var Title
	 */
	protected $spamlist;

	public function __construct( Title $spamlist ) {
		$this->spamlist = $spamlist;
	}

	/**
	 * Get an array of targets via the #target parser function.
	 *
	 * @return array[]
	 */
	public function fetchTargets() {
		$page = WikiPage::factory( $this->spamlist );
		$text = $page->getContent( RevisionRecord::RAW )->getNativeData();

		// Prep the parser
		$parserOptions = $page->makeParserOptions( 'canonical' );
		$parser = MediaWikiServices::getInstance()->getParserFactory()->create();
		$parser->firstCallInit(); // So our initial parser function is added
		// Now overwrite it
		$parser->setFunctionHook(
			'target',
			[ MassMessageHooks::class, 'storeDataParserFunction' ]
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

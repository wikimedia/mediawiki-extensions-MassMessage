<?php

namespace MediaWiki\MassMessage\Lookup;

use MediaWiki\MassMessage\MassMessageHooks;
use MediaWiki\MediaWikiServices;
use MediaWiki\Revision\RevisionRecord;
use TextContent;
use Title;

class ParserFunctionSpamlistLookup extends SpamlistLookup {

	/**
	 * @var Title
	 */
	protected $spamlist;

	/**
	 * @param Title $spamlist
	 */
	public function __construct( Title $spamlist ) {
		$this->spamlist = $spamlist;
	}

	/**
	 * Get an array of targets via the #target parser function.
	 *
	 * @return array[]
	 */
	public function fetchTargets() {
		$page = MediaWikiServices::getInstance()->getWikiPageFactory()->newFromTitle( $this->spamlist );
		$content = $page->getContent( RevisionRecord::RAW );
		/** @var TextContent $content */
		'@phan-var TextContent $content';
		$text = $content->getText();

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

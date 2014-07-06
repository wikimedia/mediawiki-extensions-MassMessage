<?php

/**
 * Functions related to target processing
 */

class MassMessageTargets {

	/**
	 * Get an array of targets given a title; returns null if invalid.
	 *
	 * Each target is an associative array with the following keys:
	 * title: The title of the target
	 * wiki: The ID of the wiki (wfWikiID() for the local wiki)
	 * site: The hostname and port (if exists) of the wiki
	 *
	 * @param Title $spamlist
	 * @param IContextSource $context
	 * @return array|null
	 */
	 public static function getTargets( Title $spamlist, $context ) {
		if ( !$spamlist->exists() && !$spamlist->inNamespace( NS_CATEGORY ) ) {
			return null;
		}

		if ( $spamlist->inNamespace( NS_CATEGORY ) ) {
			return self::getCategoryTargets( $spamlist );
		} elseif ( $spamlist->hasContentModel( 'MassMessageListContent' ) ) {
			return self::getMassMessageListContentTargets( $spamlist );
		} elseif ( $spamlist->hasContentModel( CONTENT_MODEL_WIKITEXT ) ) {
			return self::getParserFunctionTargets( $spamlist, $context );
		} else {
			return null;
		}
	}

	/**
	 * Get array of normalized targets with duplicates removed
	 * @param  array $data
	 * @return array
	 */
	public static function normalizeTargets( array $data ) {
		global $wgNamespacesToConvert;

		foreach ( $data as &$target ) {
			if ( $target['wiki'] === wfWikiID() ) {
				$title = Title::newFromText( $target['title'] );
				if ( $title === null ) {
					continue;
				}
				if ( isset( $wgNamespacesToConvert[$title->getNamespace()] ) ) {
					$title = Title::makeTitle( $wgNamespacesToConvert[$title->getNamespace()],
						$title->getText() );
				}
				$title = MassMessage::followRedirect( $title );
				if ( $title === null ) {
					continue; // Interwiki redirect
				}
				$target['title'] = $title->getPrefixedText();
			}
		}

		// Return $data with duplicates removed
		return array_unique( $data, SORT_REGULAR );
	}

	/**
	 * Get an array of targets from a category
	 * @param  Title $spamlist
	 * @return array
	 */
	public static function getCategoryTargets( Title $spamlist ) {
		global $wgCanonicalServer;

		$members = Category::newFromTitle( $spamlist )->getMembers();
		$targets = array();

		/** @var Title $member */
		foreach ( $members as $member ) {
			$targets[] = array(
				'title' => $member->getPrefixedText(),
				'wiki' => wfWikiID(),
				'site' => MassMessage::getBaseUrl( $wgCanonicalServer ),
			);
		}
		return $targets;
	}

	/**
	 * Get an array of targets from a page with the MassMessageListContent model
	 * @param Title $spamlist
	 * @return array
	 */
	public static function getMassMessageListContentTargets ( Title $spamlist ) {
		global $wgCanonicalServer;

		$targets = Revision::newFromTitle( $spamlist )->getContent()->getTargets();
		foreach ( $targets as &$target ) {
			if ( array_key_exists( 'site', $target ) ) {
				$target['wiki'] = MassMessage::getDBName( $target['site'] );
			} else {
				$target['wiki'] = wfWikiID();
				$target['site'] = MassMessage::getBaseUrl( $wgCanonicalServer );
			}
		}
		return $targets;
	}

	/**
	 * Get an array of targets via the #target parser function
	 * @param  Title $spamlist
	 * @param  IContextSource $context
	 * @return array
	 */
	public static function getParserFunctionTargets( Title $spamlist, $context ) {
		$page = WikiPage::factory( $spamlist );
		$text = $page->getContent( Revision::RAW )->getNativeData();

		// Prep the parser
		$parserOptions = $page->makeParserOptions( $context );
		$parser = new Parser();
		$parser->firstCallInit(); // So our initial parser function is added
		$parser->setFunctionHook( 'target', 'MassMessageHooks::storeDataParserFunction' ); // Now overwrite it

		// Parse
		$output = $parser->parse( $text, $spamlist, $parserOptions );
		$data = unserialize( $output->getProperty( 'massmessage-targets' ) );

		if ( $data ) {
			return $data;
		} else {
			return array(); // No parser functions on page
		}
	}

	/**
	 * Helper function to compare two targets in a MassMessageListContent delivery
	 * list for ordering in usort()
	 * @param array $a
	 * @paran array $b
	 * @return int
	 */
	public static function compareStoredTargets( $a, $b ) {
		if ( !array_key_exists( 'site', $a ) && array_key_exists( 'site', $b ) ) {
			return -1;
		} else if ( array_key_exists( 'site', $a ) && !array_key_exists( 'site', $b ) ) {
			return 1;
		} else if ( array_key_exists( 'site', $a ) && array_key_exists( 'site', $b )
			&& $a['site'] !== $b['site']
		) {
			return strcmp( $a['site'], $b['site'] );
		} else {
			return strcmp( $a['title'], $b['title'] );
		}
	}

	/**
	 * Helper function for extracting title and site strings from 'title@site'
	 * @param string $target
	 * @return array
	 */
	public static function extractFromTarget( $target ) {
		$target = trim( $target );
		$delimiterPos = strrpos( $target, '@' );
		if ( $delimiterPos !== false && $delimiterPos < strlen( $target ) ) {
			$title = substr( $target, 0, $delimiterPos );
			$site = strtolower( substr( $target, $delimiterPos+1 ) );
		} else {
			$title = $target;
			$site = '';
		}
		return array( 'title' => $title, 'site' => $site );
	}
}

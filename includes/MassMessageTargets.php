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
	 * Normalized targets are briefly cached because it can be expensive to parse PF targets on both
	 * preview and save in SpecialMassMessage.
	 *
	 * @param Title $spamlist
	 * @param bool $normalize Whether to normalize and deduplicate the targets
	 * @return array|null
	 */
	public static function getTargets( Title $spamlist, $normalize = true ) {
		global $wgMemc;

		if ( !$spamlist->exists() && !$spamlist->inNamespace( NS_CATEGORY ) ) {
			return null;
		}

		// Try to lookup cached targets
		$cacheKey = null;
		if ( !$spamlist->inNamespace( NS_CATEGORY ) ) {
			$cacheKey = wfMemcKey( 'massmessage', 'targets', $spamlist->getLatestRevId(),
				$spamlist->getTouched() );
			$cacheTargets = $wgMemc->get( $cacheKey );
			if ( $cacheTargets !== false ) {
				return $cacheTargets;
			}
		}

		if ( $spamlist->inNamespace( NS_CATEGORY ) ) {
			$targets = self::getCategoryTargets( $spamlist );
		} elseif ( $spamlist->hasContentModel( 'MassMessageListContent' ) ) {
			$targets = self::getMassMessageListContentTargets( $spamlist );
		} elseif ( $spamlist->hasContentModel( CONTENT_MODEL_WIKITEXT ) ) {
			$targets = self::getParserFunctionTargets( $spamlist );
		} else {
			$targets = null;
		}

		if ( !$targets ) {
			return $targets; // null or empty array
		}

		if ( $normalize ) {
			$normalized = self::normalizeTargets( $targets );
			if ( $cacheKey ) { // $spamlist is not a category
				$wgMemc->set( $cacheKey, $normalized, 60 * 20 );
			}
			return $normalized;
		} else {
			return $targets;
		}
	}

	/**
	 * Get array of normalized targets with duplicates removed
	 * @param array $data
	 * @return array
	 */
	protected static function normalizeTargets( array $data ) {
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
	protected static function getCategoryTargets( Title $spamlist ) {
		global $wgCanonicalServer;

		$members = Category::newFromTitle( $spamlist )->getMembers();
		$targets = [];

		/** @var Title $member */
		foreach ( $members as $member ) {
			$targets[] = [
				'title' => $member->getPrefixedText(),
				'wiki' => wfWikiID(),
				'site' => MassMessage::getBaseUrl( $wgCanonicalServer ),
			];
		}
		return $targets;
	}

	/**
	 * Get an array of targets from a page with the MassMessageListContent model
	 * @param Title $spamlist
	 * @return array
	 */
	protected static function getMassMessageListContentTargets( Title $spamlist ) {
		global $wgCanonicalServer;

		$targets = Revision::newFromTitle( $spamlist )->getContent()->getValidTargets();
		foreach ( $targets as &$target ) {
			if ( array_key_exists( 'site', $target ) ) {
				$target['wiki'] = MassMessage::getDBName( $target['site'] );
			} else {
				$target['site'] = MassMessage::getBaseUrl( $wgCanonicalServer );
				$target['wiki'] = wfWikiId();
			}
		}
		return $targets;
	}

	/**
	 * Get an array of targets via the #target parser function
	 * @param  Title $spamlist
	 * @param  IContextSource $context
	 * @return array|string if a string, it'll be the error message key
	 */
	public static function getParserFunctionTargets( Title $spamlist ) {
		global $wgMemc;
		$page = WikiPage::factory( $spamlist );

		$content = $page->getContent( Revision::RAW );
		$text = $content->getNativeData();

		$key = wfMemcKey( 'massmessage', 'targets', $page->getRevision()->getId() );
		$data = $wgMemc->get( $key );
		if ( $data !== false ) {
			return $data;
		}
		if ( $content instanceof WikitextContent ) {
			// The content type should have already been checked
			// earlier, but we'll be safe.
			$text = $content->getNativeData();
		} else {
			return 'massmessage-spamlist-doesnotexist';
		}

		// Prep the parser
		$parserOptions = $page->makeParserOptions( 'canonical' );
		$parser = new Parser();
		$parser->firstCallInit(); // So our initial parser function is added
		// Now overwrite it
		$parser->setFunctionHook(
			'target',
			'MassMessageHooks::storeDataParserFunction'
		);

		// Parse
		$output = $parser->parse( $text, $spamlist, $parserOptions );
		$data = unserialize( $output->getProperty( 'massmessage-targets' ) );

		if ( $data ) {
			$data = self::normalizeTargets( $data );
		} else {
			$data = []; // No parser functions on page
		}
		$wgMemc->set( $key, $data, 60 * 60 );
		return $data;
	}
}

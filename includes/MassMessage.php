<?php

/**
 * Some core functions needed by the extension.
 *
 * @file
 * @author Kunal Mehta
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

class MassMessage {

	/**
	 * Function to follow redirects
	 *
	 * @param $title Title
	 * @return Title|null null if the page is an interwiki redirect
	 */
	public static function followRedirect( Title $title ) {
		if ( !$title->isRedirect() ) {
			return $title;
		}
		$wikipage = WikiPage::factory( $title );

		$target = $wikipage->followRedirect();
		if ( $target instanceof Title ) {
			return $target;
		} else {
			return null; // Interwiki redirect
		}
	}

	/**
	 * Sets up the messenger account for our use if it hasn't been already.
	 * Based on code from AbuseFilter
	 * https://mediawiki.org/wiki/Extension:AbuseFilter
	 *
	 * @return User
	 */
	public static function getMessengerUser() {
		global $wgMassMessageAccountUsername;
		// Function kinda copied from the AbuseFilter
		$user = User::newFromName( $wgMassMessageAccountUsername );
		$user->load();
		if ( $user->getId() && $user->mPassword == '' && $user->mNewpassword == '' ) {
			// We've already stolen the account
			return $user;
		}

		if ( !$user->getId() ) {
			$user->addToDatabase();
			$user->saveSettings();

			// Increment site_stats.ss_users
			$ssu = new SiteStatsUpdate( 0, 0, 0, 0, 1 );
			$ssu->doUpdate();
		} else {
			// Someone already created the account, lets take it over.
			$user->setPassword( null );
			$user->setEmail( null );
			$user->saveSettings();
		}

		// Make the user a bot so it doesn't look weird
		$user->addGroup( 'bot' );

		return $user;
	}

	/**
	 * Returns the basic hostname and port using wfParseUrl
	 * @param string $url
	 * @return string
	 */
	public static function getBaseUrl( $url ) {
		static $mapping = array();

		if ( isset( $mapping[$url] ) ) {
			return $mapping[$url];
		}

		$parse = wfParseUrl( $url );
		$mapping[$url] = $parse['host'];
		if ( isset( $parse['port'] ) ) {
			$mapping[$url] .= ':' . $parse['port'];
		}
		return $mapping[$url];
	}

	/**
	 * Get database name from URL hostname
	 * Requires $wgConf to be set up properly
	 * Tries to read from cache if possible
	 * @param  string $host
	 * @return string
	 */
	public static function getDBName( $host ) {
		global $wgConf, $wgMemc;
		static $mapping = null;
		if ( $mapping === null ) {
			// Don't use wfMemcKey since it splits cache per wiki
			$key = 'massmessage:urltodb';
			$data = $wgMemc->get( $key );
			if ( $data === false ) {
				$dbs = $wgConf->getLocalDatabases();
				$mapping = array();
				foreach ( $dbs as $dbname ) {
					$url = WikiMap::getWiki( $dbname )->getCanonicalServer();
					$site = self::getBaseUrl( $url );
					$mapping[$site] = $dbname;
				}
				$wgMemc->set( $key, $mapping, 60 * 60 * 24 * 7 );
			} else {
				$mapping = $data;
			}
		}
		if ( isset( $mapping[$host] ) ) {
			return $mapping[$host];
		}
		return null; // Couldn't find anything
	}

	/**
	 * Verify that parser function data is valid and return processed data as an array
	 * @param string $page
	 * @param string $site
	 * @return array
	 */
	public static function processPFData( $page, $site ) {
		global $wgCanonicalServer, $wgAllowGlobalMessaging;

		if ( Title::newFromText( $page ) === null ) {
			return self::parserError( 'massmessage-parse-badpage', $page );
		}

		$data = array( 'title' => $page, 'site' => trim( $site ) );
		if ( $data['site'] === '' ) {
			$data['site'] = self::getBaseUrl( $wgCanonicalServer );
			$data['wiki'] = wfWikiID();
		} else {
			$data['wiki'] = self::getDBName( $data['site'] );
			if ( $data['wiki'] === null ) {
				return self::parserError( 'massmessage-parse-badurl', $site );
			}
			if ( !$wgAllowGlobalMessaging && $data['wiki'] !== wfWikiID() ) {
				return self::parserError( 'massmessage-global-disallowed' );
			}
		}
		return $data;
	}

	/**
	 * Normalize target array by following redirects and removing duplicates
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
				$title = self::followRedirect( $title );
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
	 * Get an array of targets given a title.
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
				'site' => self::getBaseUrl( $wgCanonicalServer ),
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
				$target['wiki'] = self::getDBName( $target['site'] );
			} else {
				$target['wiki'] = wfWikiID();
				$target['site'] = self::getBaseUrl( $wgCanonicalServer );
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
	 * Helper function for MassMessageHooks::ParserFunction
	 * Inspired from the Cite extension
	 * @param $key string message key
	 * @param $param string parameter for the message
	 * @return array
	 */
	public static function parserError( $key, $param = null ) {
		$msg = wfMessage( $key );
		if ( $param ) {
			$msg->params( $param );
		}
		return array (
			'<strong class="error">' .
			$msg->inContentLanguage()->plain() .
			'</strong>',
			'noparse' => false,
			'error' => true,
		);
	}

	/**
	 * Get the number of Queued messages on this site
	 * Taken from runJobs.php --group
	 * @return int
	 */
	public static function getQueuedCount() {
		$group = JobQueueGroup::singleton();
		$queue = $group->get( 'MassMessageJob' );
		$pending = $queue->getSize();
		$claimed = $queue->getAcquiredCount();
		$abandoned = $queue->getAbandonedCount();
		$active = max( $claimed - $abandoned, 0 );

		$queued = $active + $pending;
		return $queued;
	}

	/**
	 * Verify and cleanup the main user submitted data
	 * @param array &$data should have subject, message, and spamlist keys
	 * @param Status &$status
	 */
	public static function verifyData( array &$data, Status &$status ) {
		// Trim all the things!
		foreach ( $data as $k => $v ) {
			$data[$k] = trim( $v );
		}

		if ( $data['subject'] === '' ) {
			$status->fatal( 'massmessage-empty-subject' );
		}

		$spamlist = self::getSpamlist( $data['spamlist'] );
		if ( $spamlist instanceof Title ) {
			// Prep the HTML comment message
			if ( $spamlist->inNamespace( NS_CATEGORY ) ) {
				$url = $spamlist->getFullUrl();
			} else {
				$url = $spamlist->getFullURL(
					array( 'oldid' => $spamlist->getLatestRevID() ),
					false,
					PROTO_CANONICAL
				);
			}
			$data['comment'] = array(
				RequestContext::getMain()->getUser()->getName(),
				wfWikiID(),
				$url
			);
		} else { // $spamlist contains a message key for an error message
			$status->fatal( $spamlist );
		}

		if ( $data['message'] === '' ) {
			$status->fatal( 'massmessage-empty-message' );
		}

		$footer = wfMessage( 'massmessage-message-footer' )->inContentLanguage()->plain();
		if ( trim( $footer ) ) {
			// Only add the footer if it is not just whitespace
			$data['message'] .= "\n" . $footer;
		}
	}

	/**
	 * Parse and normalize the spamlist
	 *
	 * @param $title string
	 * @return Title|string string will be a error message key
	 */
	public static function getSpamlist( $title ) {
		$spamlist = Title::newFromText( $title );

		// Simply return the title if it is a category
		if ( $spamlist !== null && $spamlist->inNamespace( NS_CATEGORY ) ) {
			return $spamlist;
		}

		if ( $spamlist === null || !$spamlist->exists() ) {
			return 'massmessage-spamlist-doesnotexist';
		} else {
			// Page exists, follow a redirect if possible
			$target = self::followRedirect( $spamlist );
			if ( $target === null || !$target->exists() ) {
				return 'massmessage-spamlist-invalid'; // Interwiki redirect or non-existent page.
			} else {
				$spamlist = $target;
			}
		}

		$contentModel = $spamlist->getContentModel();

		if ( $contentModel !== 'MassMessageListContent'
			&& $contentModel !== CONTENT_MODEL_WIKITEXT
			|| $contentModel === 'MassMessageListContent'
			&& !Revision::newFromTitle( $spamlist )->getContent()->isValid()
		) {
			return 'massmessage-spamlist-invalid';
		}

		return $spamlist;
	}

	/**
	 * Log the spamming to Special:Log/massmessage
	 *
	 * @param Title $spamlist
	 * @param User $user
	 * @param string $subject
	 */
	public static function logToWiki( Title $spamlist, User $user, $subject ) {
		$logEntry = new ManualLogEntry( 'massmessage', 'send' );
		$logEntry->setPerformer( $user );
		$logEntry->setTarget( $spamlist );
		$logEntry->setComment( $subject );
		$logEntry->setParameters( array(
			'4::revid' => $spamlist->getLatestRevID(),
		) );

		$logid = $logEntry->insert();
		$logEntry->publish( $logid );
	}

	/**
	 * Send out the message!
	 * Note that this function does not perform validation on $data
	 *
	 * @param IContextSource $context
	 * @param array $data
	 * @return int how many pages were submitted
	 */
	public static function submit( IContextSource $context, array $data ) {
		$spamlist = self::getSpamlist( $data['spamlist'] );

		// Get the array of pages to deliver to.
		$pages = self::normalizeTargets( self::getTargets( $spamlist, $context ) );

		// Log it.
		self::logToWiki( $spamlist, $context->getUser(), $data['subject'] );

		// Insert it into the job queue.
		$params = array( 'data' => $data, 'pages' => $pages );
		$job = new MassMessageSubmitJob( $spamlist, $params );
		JobQueueGroup::singleton()->push( $job );

		return count( $pages );
	}

	/*
	 * Gets a regular expression that will match this wiki's
	 * timestamps as given by ~~~~.
	 *
	 * Modified from the Echo extension
	 *
	 * @throws MWException
	 * @return String regular expression fragment.
	 */
	public static function getTimestampRegex() {
		global $wgMemc, $wgParser;

		$key = wfMemcKey( 'massmessage', 'timestamp' );
		$regex = $wgMemc->get( $key );
		if ( $regex !== false ) {
			return $regex;
		}

		// Step 1: Get an exemplar timestamp
		$title = Title::newMainPage();
		$user = User::newFromName( 'Test' );
		$options = new ParserOptions;

		/** @var Parser $wgParser */
		$exemplarTimestamp = $wgParser->preSaveTransform( '~~~~~', $title, $user, $options );

		// Step 2: Generalise it
		// Trim off the timezone to replace at the end
		$output = $exemplarTimestamp;
		$tzRegex = '/\s*\(\w+\)\s*$/';
		$tzMatches = array();
		preg_match( $tzRegex, $output, $tzMatches );
		$output = preg_replace( $tzRegex, '', $output );
		$output = preg_quote( $output, '/' );
		$output = preg_replace( '/[^\d\W]+/u', '[^\d\W]+', $output );
		$output = preg_replace( '/\d+/u', '\d+', $output );

		$output .= preg_quote( $tzMatches[0] );

		if ( !preg_match( "/$output/u", $exemplarTimestamp ) ) {
			throw new MWException( "Timestamp regex does not match exemplar" );
		}

		$output = "/$output/";

		$wgMemc->set( $key, $output );

		return $output;
	}
}

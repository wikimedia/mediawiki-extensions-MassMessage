<?php

namespace MediaWiki\MassMessage;

use CentralIdLookup;
use Exception;
use JobQueueGroup;
use ManualLogEntry;
use MediaWiki\MediaWikiServices;
use ParserOptions;
use RequestContext;
use Revision;
use Status;
use Title;
use User;

/**
 * Some core functions needed by the extension.
 *
 * @file
 * @author Kunal Mehta
 * @license GPL-2.0-or-later
 */

class MassMessage {

	/**
	 * Sets up the messenger account for our use if it hasn't been already.
	 * Based on code from AbuseFilter
	 * https://mediawiki.org/wiki/Extension:AbuseFilter
	 *
	 * @return User
	 */
	public static function getMessengerUser() {
		global $wgMassMessageAccountUsername;

		$user = User::newSystemUser(
			$wgMassMessageAccountUsername, [ 'steal' => true ]
		);
		// Make the user a bot so it doesn't look weird
		if ( !in_array( 'bot', $user->getGroups() ) ) {
			$user->addGroup( 'bot' );
		}

		return $user;
	}

	/**
	 * Verify that parser function data is valid and return processed data as an array
	 * @param string $page
	 * @param string $site
	 * @return array
	 */
	public static function processPFData( $page, $site ) {
		global $wgCanonicalServer, $wgAllowGlobalMessaging;
		$titleObj = Title::newFromText( $page );
		if ( $titleObj === null ) {
			return self::parserError( 'massmessage-parse-badpage', $page );
		}

		$data = [ 'title' => $page, 'site' => trim( $site ) ];
		if ( $data['site'] === '' ) {
			$data['site'] = UrlHelper::getBaseUrl( $wgCanonicalServer );
			$data['wiki'] = wfWikiID();
		} else {
			$data['wiki'] = DatabaseLookup::getDBName( $data['site'] );
			if ( $data['wiki'] === null ) {
				return self::parserError( 'massmessage-parse-badurl', $site );
			}
			if ( !$wgAllowGlobalMessaging && $data['wiki'] !== wfWikiID() ) {
				return self::parserError( 'massmessage-global-disallowed' );
			}
		}
		if ( $data['wiki'] === wfWikiID() && $titleObj->isExternal() ) {
			// interwiki links don't work
			if ( $wgAllowGlobalMessaging ) {
				// tell them they need to use the |site= parameter
				return self::parserError( 'massmessage-parse-badexternal', $page );
			}
			// just tell them global messaging is disabled
			return self::parserError( 'massmessage-global-disallowed' );
		}
		return $data;
	}

	/**
	 * Helper function for processPFData
	 * Inspired from the Cite extension
	 * @param string $key message key
	 * @param string|null $param parameter for the message
	 * @return array
	 */
	public static function parserError( $key, $param = null ) {
		$msg = wfMessage( $key );
		if ( $param ) {
			$msg->params( $param );
		}
		return [
			'<strong class="error">' .
			$msg->inContentLanguage()->plain() .
			'</strong>',
			'noparse' => false,
			'error' => true,
		];
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

		return $active + $pending;
	}

	/**
	 * Verify and cleanup the main user submitted data
	 * @param array &$data should have subject, message, and spamlist keys
	 * @param Status $status
	 */
	public static function verifyData( array &$data, Status $status ) {
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
				$url = $spamlist->getFullURL();
			} else {
				$url = $spamlist->getFullURL(
					[ 'oldid' => $spamlist->getLatestRevID() ],
					false,
					PROTO_CANONICAL
				);
			}
			$data['comment'] = [
				RequestContext::getMain()->getUser()->getName(),
				wfWikiID(),
				$url
			];
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
	 * @param string $title
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
		}

		// Page exists, follow a redirect if possible
		$target = UrlHelper::followRedirect( $spamlist );
		if ( $target === null || !$target->exists() ) {
			return 'massmessage-spamlist-invalid'; // Interwiki redirect or non-existent page.
		}
		$spamlist = $target;

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
		$logEntry->setParameters( [
			'4::revid' => $spamlist->getLatestRevID(),
		] );

		$logid = $logEntry->insert();
		$logEntry->publish( $logid );
	}

	/**
	 * Send out the message!
	 * Note that this function does not perform validation on $data
	 *
	 * @param User $user who the message was from (for logging)
	 * @param array $data
	 * @return int number of pages delivered to
	 */
	public static function submit( User $user, array $data ) {
		$spamlist = self::getSpamlist( $data['spamlist'] );

		// Get the array of pages to deliver to.
		$pages = SpamlistLookup::getTargets( $spamlist );

		// Log it.
		self::logToWiki( $spamlist, $user, $data['subject'] );

		$data += [
			'userId' => CentralIdLookup::factory()
				->centralIdFromLocalUser( $user, CentralIdLookup::AUDIENCE_RAW ),
			'originWiki' => wfWikiID(),
		];

		// Insert it into the job queue.
		$params = [
			'data' => $data,
			'pages' => $pages,
			'class' => MassMessageJob::class,
		];
		$job = new MassMessageSubmitJob( $spamlist, $params );
		JobQueueGroup::singleton()->push( $job );

		return count( $pages );
	}

	/**
	 * Gets a regular expression that will match this wiki's
	 * timestamps as given by ~~~~.
	 *
	 * Modified from the Echo extension
	 *
	 * @throws Exception
	 * @return String regular expression fragment.
	 */
	public static function getTimestampRegex() {
		$cache = MediaWikiServices::getInstance()->getMainWANObjectCache();

		return $cache->getWithSetCallback(
			$cache->makeKey( 'massmessage', 'timestamp' ),
			$cache::TTL_WEEK,
			function () {
				// Step 1: Get an exemplar timestamp
				$title = Title::newMainPage();
				$user = User::newFromName( 'Test' );
				$options = new ParserOptions( $user );

				$exemplarTimestamp =
					MediaWikiServices::getInstance()->getParser()
						->preSaveTransform( '~~~~~', $title, $user, $options );

				// Step 2: Generalise it
				// Trim off the timezone to replace at the end
				$output = $exemplarTimestamp;
				$tzRegex = '/\s*\(\w+\)\s*$/';
				$output = preg_replace( $tzRegex, '', $output );
				$output = preg_quote( $output, '/' );
				$output = preg_replace( '/[^\d\W]+/u', '[^\d\W]+', $output );
				$output = preg_replace( '/\d+/u', '\d+', $output );

				$tzMatches = [];
				if ( preg_match( $tzRegex, $exemplarTimestamp, $tzMatches ) ) {
					$output .= preg_quote( $tzMatches[0] );
				}

				if ( !preg_match( "/$output/u", $exemplarTimestamp ) ) {
					throw new Exception( "Timestamp regex does not match exemplar" );
				}

				return "/$output/";
			}
		);
	}
}

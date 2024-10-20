<?php

namespace MediaWiki\MassMessage;

use LogicException;
use ManualLogEntry;
use MediaWiki\Extension\Translate\PageTranslation\TranslatablePage;
use MediaWiki\MainConfigNames;
use MediaWiki\MassMessage\Job\MassMessageJob;
use MediaWiki\MassMessage\Job\MassMessageSubmitJob;
use MediaWiki\MassMessage\Lookup\DatabaseLookup;
use MediaWiki\MassMessage\Lookup\SpamlistLookup;
use MediaWiki\MassMessage\RequestProcessing\MassMessageRequest;
use MediaWiki\MediaWikiServices;
use MediaWiki\Parser\ParserOptions;
use MediaWiki\Registration\ExtensionRegistry;
use MediaWiki\Title\Title;
use MediaWiki\User\CentralId\CentralIdLookup;
use MediaWiki\User\User;
use MediaWiki\WikiMap\WikiMap;

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
		$services = MediaWikiServices::getInstance();
		$userGroupManager = $services->getUserGroupManager();
		$userFactory = $services->getUserFactory();

		$accountUsername = $services->getMainConfig()->get( 'MassMessageAccountUsername' );

		// Only assign the bot flag if newSystemUser would either create
		//  or steal the account with the username specified.
		$user = $userFactory->newFromName( $accountUsername );
		$shouldAssignBotFlag = !$user->isRegistered() || !$user->isSystemUser();

		$user = User::newSystemUser(
			$accountUsername, [ 'steal' => true ]
		);
		// Make the user a bot so it doesn't look weird when the account was stolen
		//  or created.
		if ( $shouldAssignBotFlag && !in_array( 'bot', $userGroupManager->getUserGroups( $user ) ) ) {
			$userGroupManager->addUserToGroup( $user, 'bot' );
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
		$config = MediaWikiServices::getInstance()->getMainConfig();

		$titleObj = Title::newFromText( $page );
		if ( $titleObj === null ) {
			return self::parserError( 'massmessage-parse-badpage', $page );
		}

		$currentWikiId = WikiMap::getCurrentWikiId();
		$data = [ 'title' => $page, 'site' => trim( $site ) ];
		if ( $data['site'] === '' ) {
			$data['site'] = UrlHelper::getBaseUrl( $config->get( MainConfigNames::CanonicalServer ) );
			$data['wiki'] = $currentWikiId;
		} else {
			$data['wiki'] = DatabaseLookup::getDBName( $data['site'] );
			if ( $data['wiki'] === null ) {
				return self::parserError( 'massmessage-parse-badurl', $site );
			}
			if ( !$config->get( 'AllowGlobalMessaging' ) && $data['wiki'] !== $currentWikiId ) {
				return self::parserError( 'massmessage-global-disallowed' );
			}
		}
		if ( $data['wiki'] === $currentWikiId && $titleObj->isExternal() ) {
			// interwiki links don't work
			if ( $config->get( 'AllowGlobalMessaging' ) ) {
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
		$group = MediaWikiServices::getInstance()->getJobQueueGroupFactory()->makeJobQueueGroup();
		$queue = $group->get( 'MassMessageJob' );
		$pending = $queue->getSize();
		$claimed = $queue->getAcquiredCount();
		$abandoned = $queue->getAbandonedCount();
		$active = max( $claimed - $abandoned, 0 );

		return $active + $pending;
	}

	/**
	 * Log the spamming to Special:Log/massmessage
	 *
	 * @param Title $spamlist
	 * @param User $user
	 * @param string $subject
	 * @param string $pageMessage
	 */
	public static function logToWiki(
		Title $spamlist,
		User $user,
		string $subject,
		string $pageMessage
	): void {
		$logEntry = new ManualLogEntry( 'massmessage', 'send' );
		$logEntry->setPerformer( $user );
		$logEntry->setTarget( $spamlist );
		$logEntry->setComment( $subject );
		$logEntry->setParameters( [
			'4::revid' => $spamlist->getLatestRevID(),
			'5::pageMessage' => $pageMessage
		] );

		$logid = $logEntry->insert();
		$logEntry->publish( $logid );
	}

	/**
	 * Send out the message!
	 * Note that this function does not perform validation on $data
	 *
	 * @param User $user who the message was from (for logging)
	 * @param MassMessageRequest $request
	 * @return int number of pages delivered to
	 */
	public static function submit( User $user, MassMessageRequest $request ): int {
		// Get the array of pages to deliver to.
		$pages = SpamlistLookup::getTargets( $request->getSpamList() );

		// Log it.
		self::logToWiki( $request->getSpamList(), $user, $request->getSubject(), $request->getPageMessage() );

		$pageTitle = null;
		$sourcePageLanguage = null;
		$isSourceTranslationPage = false;
		if ( $request->hasPageMessage() ) {
			$pageTitle = Services::getInstance()
				->getLocalMessageContentFetcher()
				->getTitle( $request->getPageMessage() )
				->getValue();
			$isSourceTranslationPage = self::isSourceTranslationPage( $pageTitle );
			if ( $isSourceTranslationPage ) {
				$sourcePageLanguage = $pageTitle->getPageLanguage()->getCode();
			}
		}

		$services = MediaWikiServices::getInstance();
		$originWiki = WikiMap::getCurrentWikiId();

		$data = $request->getSerializedData();
		$data += [
			'userId' => $services->getCentralIdLookup()
				->centralIdFromLocalUser( $user, CentralIdLookup::AUDIENCE_RAW ),
			'originWiki' => $originWiki,
			'isSourceTranslationPage' => $isSourceTranslationPage,
			'translationPageSourceLanguage' => $sourcePageLanguage,
			'pageMessageTitle' => $pageTitle ? $pageTitle->getPrefixedText() : null
		];

		// Insert it into the job queue.
		$params = [
			'data' => $data,
			'pages' => $pages,
			'class' => MassMessageJob::class,
		];

		$job = new MassMessageSubmitJob( $request->getSpamList(), $params );
		$services->getJobQueueGroupFactory()->makeJobQueueGroup( $originWiki )->push( $job );

		return count( $pages );
	}

	/**
	 * Gets a regular expression that will match this wiki's
	 * timestamps as given by ~~~~.
	 *
	 * Modified from the Echo extension
	 *
	 * @return string regular expression fragment.
	 */
	public static function getTimestampRegex() {
		$cache = MediaWikiServices::getInstance()->getMainWANObjectCache();

		return $cache->getWithSetCallback(
			$cache->makeKey( 'massmessage', 'timestamp' ),
			$cache::TTL_WEEK,
			static function () {
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
					throw new LogicException( "Timestamp regex does not match exemplar" );
				}

				return "/$output/";
			}
		);
	}

	/**
	 * Checks if a title is a source translation page
	 *
	 * @param Title $title
	 * @return bool
	 */
	public static function isSourceTranslationPage( Title $title ): bool {
		return ExtensionRegistry::getInstance()->isLoaded( 'Translate' ) &&
			// @phan-suppress-next-line PhanUndeclaredClassMethod
			TranslatablePage::isSourcePage( $title );
	}
}

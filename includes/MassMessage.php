<?php

namespace MediaWiki\MassMessage;

use CentralIdLookup;
use ContentHandler;
use Exception;
use ExtensionRegistry;
use JobQueueGroup;
use Language;
use ManualLogEntry;
use MediaWiki\MassMessage\Job\MassMessageJob;
use MediaWiki\MassMessage\Job\MassMessageSubmitJob;
use MediaWiki\MediaWikiServices;
use MediaWiki\Revision\SlotRecord;
use ParserOptions;
use RequestContext;
use Status;
use Title;
use User;
use WikiMap;

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

		$currentWikiId = WikiMap::getCurrentWikiId();
		$data = [ 'title' => $page, 'site' => trim( $site ) ];
		if ( $data['site'] === '' ) {
			$data['site'] = UrlHelper::getBaseUrl( $wgCanonicalServer );
			$data['wiki'] = $currentWikiId;
		} else {
			$data['wiki'] = DatabaseLookup::getDBName( $data['site'] );
			if ( $data['wiki'] === null ) {
				return self::parserError( 'massmessage-parse-badurl', $site );
			}
			if ( !$wgAllowGlobalMessaging && $data['wiki'] !== $currentWikiId ) {
				return self::parserError( 'massmessage-global-disallowed' );
			}
		}
		if ( $data['wiki'] === $currentWikiId && $titleObj->isExternal() ) {
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
				WikiMap::getCurrentWikiId(),
				$url
			];
		} else { // $spamlist contains a message key for an error message
			$status->fatal( $spamlist );
		}

		$data['page-message'] = $data['page-message'] ?? '';
		$data['message'] = $data['message'] ?? '';

		// Check and fetch the page message
		$pageMessage = null;
		if ( $data['page-message'] !== '' ) {
			$pageMessageStatus = self::getLocalContentByTitle( $data['page-message'] );
			if ( $pageMessageStatus->isOK() ) {
				$pageMessage = $pageMessageStatus->getValue();
				if ( $pageMessage === '' ) {
					$status->fatal( 'massmessage-page-message-empty', $data['page-message'] );
				}
			} else {
				$status->merge( $pageMessageStatus );
			}
		}

		if ( $data['message'] === '' && $pageMessage === null ) {
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
			&& !MediaWikiServices::getInstance()
				->getRevisionLookup()
				->getRevisionByTitle( $spamlist )
				->getContent( SlotRecord::MAIN )
				->isValid()
		) {
			return 'massmessage-spamlist-invalid';
		}

		return $spamlist;
	}

	/**
	 * Helper method to get the page message for a page from the same wiki.
	 *
	 * @param string $messageTitle
	 * @param int $pageNamespace
	 * @return Status
	 */
	public static function getLocalContentByTitle(
		string $messageTitle, int $pageNamespace = NS_MAIN
	): Status {
		$pageMessageStatus = self::getLocalContentTitle( $messageTitle, $pageNamespace );
		if ( $pageMessageStatus->isOK() ) {
			$pageMessageStatus = self::getLocalContent( $pageMessageStatus->getValue() );
		}

		return $pageMessageStatus;
	}

	/**
	 * Fetch the page title given the title string
	 *
	 * @param string $title
	 * @param int $pageNamespace
	 * @return Status
	 */
	public static function getLocalContentTitle(
		string $title, int $pageNamespace = NS_MAIN
	): Status {
		$pageTitle = Title::makeTitleSafe( $pageNamespace, $title );

		if ( $pageTitle === null ) {
			return Status::newFatal(
				'massmessage-page-message-invalid', "$pageNamespace::$title"
			);
		} elseif ( !$pageTitle->exists() ) {
			return Status::newFatal(
				'massmessage-page-message-not-found',
				$pageTitle->getPrefixedText(),
				WikiMap::getCurrentWikiId()
			);
		}

		return Status::newGood( $pageTitle );
	}

	/**
	 * Fetch the page content with the given title from the same wiki.
	 *
	 * @param Title $pageTitle
	 * @return Status
	 */
	public static function getLocalContent( Title $pageTitle ): Status {
		$revision = MediaWikiServices::getInstance()
			->getRevisionStore()->getRevisionByTitle( $pageTitle );

		if ( $revision === null ) {
			return Status::newFatal(
				'massmessage-page-message-no-revision',
				$pageTitle->getPrefixedText()
			);
		}

		$wiki = ContentHandler::getContentText( $revision->getContent( SlotRecord::MAIN ) );

		if ( $wiki === null ) {
			return Status::newFatal(
				'massmessage-page-message-no-revision-content',
				$pageTitle->getPrefixedText(),
				$revision->getId()
			);
		}

		return Status::newGood( $wiki );
	}

	/**
	 * Fetch the page content with the given title from the given wiki.
	 *
	 * @param Title $pageTitle
	 * @param string $wikiId
	 * @return Status
	 */
	public static function getRemoteContent(
		Title $pageTitle, string $wikiId
	): Status {
		$apiUrl = self::getApiEndpoint( $wikiId );
		if ( !$apiUrl ) {
			return Status::newFatal(
				'massmessage-page-message-wiki-not-found',
				$wikiId,
				$pageTitle->getPrefixedURL()
			);
		}

		$queryParams = [
			'action' => 'parse',
			'format' => 'json',
			'prop' => 'wikitext',
			'page' => $pageTitle->getPrefixedText(),
			'formatversion' => 2
		];

		$options = [
			'method' => 'GET',
			'timeout' => 15
		];

		$apiUrl .= '?' . http_build_query( $queryParams );
		$req = MediaWikiServices::getInstance()->getHttpRequestFactory()
			->create( $apiUrl, $options, __METHOD__ );

		$status = $req->execute();
		if ( !$status->isOK() ) {
			$error = $req->getContent();
			return Status::newFatal(
				"massmessage-page-message-parse-error-in-wiki",
				$wikiId,
				$pageTitle->getPrefixedText(),
				$error
			);
		}

		$json = $req->getContent();
		$response = json_decode( $json, true );

		if ( ( $response['error']['code'] ?? '' ) === 'missingtitle' ) {
			// Page was not found
			return Status::newFatal(
				'massmessage-page-message-not-found-in-wiki',
				$wikiId,
				$pageTitle->getPrefixedText()
			);
		} elseif ( isset( $response['error']['info'] ) ) {
			// We got another error from the API.
			return Status::newFatal(
				'massmessage-page-message-parse-invalid-in-wiki',
				$wikiId,
				$pageTitle->getPrefixedText(),
				$response['error']['info']
			);
		} elseif ( !isset( $response['parse'] ) ) {
			// Sanity check
			return Status::newFatal(
				'massmessage-page-message-parse-invalid-in-wiki',
				$wikiId,
				$pageTitle->getPrefixedText(),
				$json
			);
		}

		return Status::newGood( $response['parse']['wikitext'] );
	}

	/**
	 * Get content for a target language from wiki, using fallbacks if necessary
	 *
	 * @param string $titleStr
	 * @param string $targetLangCode
	 * @param string $sourceLangCode
	 * @param string $wikiId
	 * @return Status
	 */
	public static function getContentWithFallback(
		string $titleStr, string $targetLangCode, string $sourceLangCode, string $wikiId
	): Status {
		if ( !Language::isKnownLanguageTag( $targetLangCode ) ) {
			return Status::newFatal( 'massmessage-invalid-lang', $targetLangCode );
		}

		// Identify languages to fetch
		$langFallback = MediaWikiServices::getInstance()->getLanguageFallback();
		$fallbackChain = array_merge(
			[ $targetLangCode ],
			$langFallback->getAll( $targetLangCode )
		);

		foreach ( $fallbackChain as $langCode ) {
			$titleStrWithLang = $titleStr . '/' . $langCode;
			$contentStatus = self::getContent( $titleStrWithLang, $wikiId );

			if ( $contentStatus->isOK() ) {
				return $contentStatus;
			}

			// Got an unknown error, let's stop looking for other fallbacks
			if ( !self::isNotFoundError( $contentStatus ) ) {
				break;
			}
		}

		// No language or fallback found or there was an error, go with source language
		$langSuffix = '';
		if ( $sourceLangCode ) {
			$langSuffix = "/$sourceLangCode";
		}

		return self::getContent( $titleStr . $langSuffix, $wikiId );
	}

	/**
	 * Helper method that uses the database or API to fetch content based on the wiki.
	 *
	 * @param string $titleStr
	 * @param string $wikiId
	 * @return Status
	 */
	public static function getContent( string $titleStr, string $wikiId ): Status {
		$isCurrentWiki = WikiMap::getCurrentWikiId() === $wikiId;
		$title = Title::newFromText( $titleStr );
		if ( $isCurrentWiki ) {
			$contentStatus = self::getLocalContentByTitle( $title, $title->getNamespace() );
		} else {
			$contentStatus = self::getRemoteContent( $title, $wikiId );
		}

		return $contentStatus;
	}

	/**
	 * Checks if a given Status is a not found error.
	 *
	 * @param Status $status
	 * @return bool
	 */
	private static function isNotFoundError( Status $status ): bool {
		$notFoundErrors = [
			'massmessage-page-message-not-found', 'massmessage-page-message-not-found-in-wiki'
		];
		$errors = $status->getErrorsArray();
		if ( $errors ) {
			foreach ( $errors as $error ) {
				if ( in_array( $error[0], $notFoundErrors ) ) {
					return true;
				}
			}
		}

		return false;
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

		$isSourceTranslationPage = false;

		/**
		 * @var Title
		 */
		$pageTitle = null;
		$sourcePageLanguage = null;
		if ( $data['page-message'] !== '' ) {
			$pageTitle = self::getLocalContentTitle( $data['page-message'] )->getValue();
			$isSourceTranslationPage = self::isSourceTranslationPage( $pageTitle );
			if ( $isSourceTranslationPage ) {
				$sourcePageLanguage = $pageTitle->getPageLanguage()->getCode();
			}
		}

		$data += [
			'userId' => CentralIdLookup::factory()
				->centralIdFromLocalUser( $user, CentralIdLookup::AUDIENCE_RAW ),
			'originWiki' => WikiMap::getCurrentWikiId(),
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
	 * @return string regular expression fragment.
	 * @throws Exception
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

	/**
	 * Checks if a title is a source translation page
	 *
	 * @param Title $title
	 * @return bool
	 */
	public static function isSourceTranslationPage( Title $title ): bool {
		return ExtensionRegistry::getInstance()->isLoaded( 'Translate' ) &&
			// @phan-suppress-next-line PhanUndeclaredClassMethod
			\TranslatablePage::isSourcePage( $title );
	}

	/**
	 * Used to append the message and content of the page
	 *
	 * @param string $message Custom message
	 * @param string $pageContent Content of the page to be sent as message
	 * @return string
	 */
	public static function appendMessageAndPage( string $message, string $pageContent ): string {
		return $message . "\n\n----\n\n" . $pageContent;
	}

	public static function getApiEndpoint( string $wiki ): ?string {
		global $wgConf;
		$wgConf->loadFullData();

		$siteFromDB = $wgConf->siteFromDB( $wiki );
		[ $major, $minor ] = $siteFromDB;

		if ( $major === null ) {
			return null;
		}

		$server = $wgConf->get( 'wgServer', $wiki, [ 'lang' => $minor, 'site' => $major ] );
		$scriptPath = $wgConf->get( 'wgScriptPath', $wiki, [ 'lang' => $minor, 'site' => $major ] );

		$apiPath = wfExpandUrl( $server . $scriptPath . '/api.php', PROTO_INTERNAL );

		return $apiPath;
	}
}

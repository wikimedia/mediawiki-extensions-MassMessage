<?php

namespace MediaWiki\MassMessage;

use CentralIdLookup;
use ContentHandler;
use Exception;
use ExtensionRegistry;
use Html;
use JobQueueGroup;
use Language;
use ManualLogEntry;
use MediaWiki\MassMessage\Job\MassMessageJob;
use MediaWiki\MassMessage\Job\MassMessageSubmitJob;
use MediaWiki\MassMessage\Lookup\DatabaseLookup;
use MediaWiki\MassMessage\Lookup\SpamlistLookup;
use MediaWiki\MassMessage\RequestProcessing\MassMessageRequest;
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
		$userGroupManager = MediaWikiServices::getInstance()->getUserGroupManager();

		$user = User::newSystemUser(
			$wgMassMessageAccountUsername, [ 'steal' => true ]
		);
		// Make the user a bot so it doesn't look weird
		if ( !in_array( 'bot', $userGroupManager->getUserGroups( $user ) ) ) {
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
	 * @param array $data should have subject, message, and spamlist keys
	 * @return Status
	 */
	public static function verifyData( array $data ): Status {
		// Trim all the things!
		foreach ( $data as $k => $v ) {
			$data[$k] = trim( $v );
		}

		$status = new Status();
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
		$data['page-section'] = $data['page-section'] ?? '';
		$data['message'] = $data['message'] ?? '';

		// Check and fetch the page message
		$pageMessage = null;
		if ( $data['page-message'] !== '' ) {
			$pageMessageStatus = self::getContent(
				$data['page-message'],
				WikiMap::getCurrentWikiId(),
				$data['page-section']
			);

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

		if ( $status->isOK() ) {
			$status->setResult(
				true,
				new MassMessageRequest(
					$spamlist,
					$data['subject'],
					$data['page-message'],
					$data['page-section'],
					$data['message'],
					$data['comment']
				)
			);
		}

		return $status;
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
	 * Fetch the page title given the title string
	 *
	 * @param string $title
	 * @return Status
	 */
	public static function getLocalContentTitle( string $title ): Status {
		$pageTitle = Title::newFromText( $title );

		if ( $pageTitle === null ) {
			return Status::newFatal(
				'massmessage-page-message-invalid', $title
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
	 * @return Status Values is LanguageAwareText or null on failure
	 */
	public static function getLocalContent( Title $pageTitle ): Status {
		if ( !$pageTitle->exists() ) {
			return Status::newFatal(
				'massmessage-page-message-not-found',
				$pageTitle->getPrefixedText(),
				WikiMap::getCurrentWikiId()
			);
		}

		$revision = MediaWikiServices::getInstance()
			->getRevisionStore()->getRevisionByTitle( $pageTitle );

		if ( $revision === null ) {
			return Status::newFatal(
				'massmessage-page-message-no-revision',
				$pageTitle->getPrefixedText()
			);
		}

		$wikitext = ContentHandler::getContentText( $revision->getContent( SlotRecord::MAIN ) );

		if ( $wikitext === null ) {
			return Status::newFatal(
				'massmessage-page-message-no-revision-content',
				$pageTitle->getPrefixedText(),
				$revision->getId()
			);
		}

		$content = new LanguageAwareText(
			$wikitext,
			$pageTitle->getPageLanguage()->getCode(),
			$pageTitle->getPageLanguage()->getDir()
		);

		return Status::newGood( $content );
	}

	/**
	 * Fetch the page content with the given title from the given wiki.
	 *
	 * @param string $pageTitle
	 * @param string $wikiId
	 * @return Status Values is LanguageAwareText or null on failure
	 */
	public static function getRemoteContent(
		string $pageTitle, string $wikiId
	): Status {
		$apiUrl = self::getApiEndpoint( $wikiId );
		if ( !$apiUrl ) {
			return Status::newFatal(
				'massmessage-page-message-wiki-not-found',
				$wikiId,
				$pageTitle
			);
		}

		$queryParams = [
			'action' => 'query',
			'format' => 'json',
			'prop' => 'info|revisions',
			'rvprop' => 'content',
			'rvslots' => 'main',
			'titles' => $pageTitle,
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
			// FIXME: Formatting is broken here, needs to be improved.
			return Status::newFatal(
				"massmessage-page-message-fetch-error-in-wiki",
				$wikiId,
				$pageTitle,
				$status->getMessage()->text()
			);
		}

		$json = $req->getContent();
		$response = json_decode( $json, true );
		if ( $response === null ) {
			return Status::newFatal(
				"massmessage-page-message-parsing-error-in-wiki",
				$wikiId,
				$pageTitle,
				json_last_error_msg()
			);
		}

		return self::parseQueryApiResponse( $response, $wikiId, $pageTitle, $json );
	}

	/**
	 * @param array $response
	 * @param string $wikiId
	 * @param string $pageTitle
	 * @param string $json
	 * @return Status
	 */
	private static function parseQueryApiResponse(
		array $response,
		string $wikiId,
		string $pageTitle,
		string $json
	): Status {
		// Example response:
		// {
		//   "batchcomplete": true,
		//   "query": {
		//     "pages": [ {
		//       "pageid": 11285354,
		//       "ns": 0,
		//       "title": "Tech/News/2021/12",
		//       "contentmodel": "wikitext",
		//       "pagelanguage": "en",
		//       "pagelanguagehtmlcode": "en",
		//       "pagelanguagedir": "ltr",
		//       "touched": "2021-03-23T06:05:06Z",
		//       "lastrevid": 21247464,
		//       "length": 4585,
		//       "revisions": [ {
		//         "slots": {
		//           "main": {
		//             "contentmodel": "wikitext",
		//             "contentformat": "text/x-wiki",
		//             "content": "[...]"
		//           }
		//         }
		//       } ]
		//     } ]
		//   }
		// }

		$pages = $response['query']['pages'] ?? [];
		if ( isset( $response['error']['info'] ) || count( $pages ) !== 1 ) {
			return Status::newFatal(
				'massmessage-page-message-parse-invalid-in-wiki',
				$wikiId,
				$pageTitle,
				$response['error']['info'] ?? $json
			);
		}

		// Take first and only one out of the list
		$page = current( $pages );

		if ( isset( $page['missing'] ) ) {
			// Page was not found
			return Status::newFatal(
				'massmessage-page-message-not-found-in-wiki',
				$wikiId,
				$pageTitle
			);
		}

		$content = new LanguageAwareText(
			$page['revisions'][0]['slots']['main']['content'],
			$page['pagelanguage'],
			$page['pagelanguagedir']
		);

		return Status::newGood( $content );
	}

	/**
	 * Get content for a target language from wiki, using fallbacks if necessary
	 *
	 * @param string $titleStr
	 * @param string $targetLangCode
	 * @param string $sourceLangCode
	 * @param string $wikiId
	 * @param ?string $pageSection
	 * @return Status Values is LanguageAwareText or null on failure
	 */
	public static function getContentWithFallback(
		string $titleStr,
		string $targetLangCode,
		string $sourceLangCode,
		string $wikiId,
		?string $pageSection
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
			$contentStatus = self::getContent( $titleStrWithLang, $wikiId, $pageSection );

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

		return self::getContent( $titleStr . $langSuffix, $wikiId, $pageSection );
	}

	/**
	 * Helper method that uses the database or API to fetch content based on the wiki.
	 *
	 * @param string $titleStr
	 * @param string $wikiId
	 * @param string|null $section
	 * @return Status Values is LanguageAwareText or null on failure
	 */
	public static function getContent( string $titleStr, string $wikiId, ?string $section = null ): Status {
		$isCurrentWiki = WikiMap::getCurrentWikiId() === $wikiId;
		$title = Title::newFromText( $titleStr );
		if ( $title === null ) {
			return Status::newFatal(
				'massmessage-page-message-invalid', $titleStr
			);
		}
		if ( $isCurrentWiki ) {
			$contentStatus = self::getLocalContent( $title );
		} else {
			$contentStatus = self::getRemoteContent( $titleStr, $wikiId );
		}

		if ( $contentStatus->isOK() && $section !== null && $section !== '' ) {
			return self::getLabeledSectionContent( $contentStatus->getValue(), $section );
		}

		return $contentStatus;
	}

	public static function getLabeledSectionContent(
		LanguageAwareText $content,
		string $label
	): Status {
		$wikitext = $content->getWikitext();

		// I looked into LabeledSectionTransclusion and it is not reusable here without a lot of
		// rework -NL
		$matches = [];
		$label = preg_quote( $label, '~' );
		$ok = preg_match_all(
			"~<section[^>]+begin\s*=\s*{$label}[^>]+>.*?<section[^>]+end\s*=\s*{$label}[^>]+>~s",
			$wikitext,
			$matches
		);

		if ( $ok < 1 ) {
			return Status::newFatal( 'massmessage-page-section-invalid' );
		}

		// Include section tags for backwards compatibility.
		// https://phabricator.wikimedia.org/T254481#6865334
		// In case there are multiple sections with same label, there will be multiple wrappers too.
		// Because LabelsedSectionTransclusion supports that natively, I see no reason to try to
		// simplify it to include only one wrapper.
		$sectionContent = new LanguageAwareText(
			trim( implode( "", $matches[0] ) ),
			$content->getLanguageCode(),
			$content->getLanguageDirection()
		);
		return Status::newGood( $sectionContent );
	}

	public static function getLabeledSections( string $pagetext ): array {
		preg_match_all(
			'~<section[^>]+begin\s*=\s*([^ /]+)[^>]+>(.*?)<section[^>]+end\s*=\s*\\1~s',
			$pagetext,
			$matches
		);
		return array_unique( $matches[1] );
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
			$pageTitle = self::getLocalContentTitle( $request->getPageMessage() )->getValue();
			$isSourceTranslationPage = self::isSourceTranslationPage( $pageTitle );
			if ( $isSourceTranslationPage ) {
				$sourcePageLanguage = $pageTitle->getPageLanguage()->getCode();
			}
		}

		$data = $request->getSerializedData();
		$data += [
			'userId' => MediaWikiServices::getInstance()->getCentralIdLookup()
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

		$job = new MassMessageSubmitJob( $request->getSpamList(), $params );
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
	 * Compose the full text from a custom message and from a page content.
	 *
	 * Adds language tagging if necessary. Includes a comment about who is the sender.
	 *
	 * @param string $customMessageText
	 * @param LanguageAwareText|null $pageContent
	 * @param Language|null $targetPageLanguage Suppress language wrapping if source and target
	 *   language match
	 * @param array $commentParams
	 * @return string
	 */
	public static function composeFullMessage(
		string $customMessageText,
		?LanguageAwareText $pageContent,
		?Language $targetPageLanguage,
		array $commentParams
	): string {
		$fullMessageText = '';

		if ( $pageContent ) {
			if ( !$targetPageLanguage
				|| $targetPageLanguage->getCode() !== $pageContent->getLanguageCode()
			) {
				// Wrap page contents if it differs from target page's language. Ideally the
				// message contents would be wrapped too, but we do not know its language.
				$fullMessageText .= Html::rawElement(
					'div',
					[
						'lang' => $pageContent->getLanguageCode(),
						'dir' => $pageContent->getLanguageDirection(),
						// This class is needed for proper rendering of list items (and maybe more)
						'class' => 'mw-content-' . $pageContent->getLanguageDirection()
					],
					"\n" . $pageContent->getWikitext() . "\n"
				);
			} else {
				$fullMessageText = $pageContent->getWikitext();
			}
		}

		// If either is empty, the extra new lines will be trimmed
		$fullMessageText = trim( $fullMessageText . "\n\n" . $customMessageText );

		$commentMessage = wfMessage( 'massmessage-hidden-comment' )->params( $commentParams );
		if ( $targetPageLanguage ) {
			$commentMessage = $commentMessage->inLanguage( $targetPageLanguage );
		}
		$fullMessageText .= "\n" . $commentMessage->text();

		return $fullMessageText;
	}

	public static function getApiEndpoint( string $wiki ): ?string {
		global $wgConf;
		$wgConf->loadFullData();

		$siteFromDB = $wgConf->siteFromDB( $wiki );
		[ $major, $minor ] = $siteFromDB;

		if ( $major === null ) {
			return null;
		}

		$server = $wgConf->get( 'wgServer', $wiki, null, [ 'lang' => $minor, 'site' => $major ] );
		$scriptPath = $wgConf->get( 'wgScriptPath', $wiki, null, [ 'lang' => $minor, 'site' => $major ] );

		$apiPath = wfExpandUrl( $server . $scriptPath . '/api.php', PROTO_INTERNAL );

		return $apiPath;
	}
}

<?php

namespace MediaWiki\MassMessage\Job;

use ApiMain;
use ApiMessage;
use ApiUsageException;
use CentralIdLookup;
use ChangeTags;
use DeferredUpdates;
use DerivativeRequest;
use ExtensionRegistry;
use Job;
use LqtDispatch;
use ManualLogEntry;
use MediaWiki\MassMessage\MassMessage;
use MediaWiki\MassMessage\MassMessageHooks;
use MediaWiki\MassMessage\PageMessage\PageMessageBuilderResult;
use MediaWiki\MassMessage\Services;
use MediaWiki\MassMessage\UrlHelper;
use MediaWiki\MediaWikiServices;
use RequestContext;
use Title;
use User;
use WikiMap;
use WikiPage;

/**
 * Job Queue class to send a message to a user.
 *
 * Based on code from TranslationNotifications
 * https://mediawiki.org/wiki/Extension:TranslationNotifications
 *
 * @file
 * @ingroup JobQueue
 * @author Kunal Mehta
 * @license GPL-2.0-or-later
 */

class MassMessageJob extends Job {

	private const STRIP_TILDES = true;

	/**
	 * @var bool Whether to use sender account (if possible)
	 * TODO: Expose this as a configurable option (T71954)
	 */
	private $useSenderUser = false;

	public function __construct( Title $title, array $params ) {
		parent::__construct( 'MassMessageJob', $title, $params );
		$this->removeDuplicates = true;
		// Create a fresh Title object so namespaces are evaluated
		// in the context of the target site. See T59464.
		// Note that jobs created previously might not have a
		// title param, so check for that.
		if ( isset( $params['title'] ) ) {
			$this->title = Title::newFromText( $params['title'] );
		} else {
			$this->title = $title;
		}
	}

	/**
	 * Execute the job.
	 *
	 * @return bool
	 */
	public function run() {
		$status = $this->sendMessage();
		if ( $status !== true ) {
			$this->setLastError( $status );
			return false;
		}

		return true;
	}

	/**
	 * @return User
	 */
	protected function getUser() {
		if ( $this->useSenderUser && isset( $this->params['userId'] ) ) {
			$services = MediaWikiServices::getInstance();
			$user = $services
				->getCentralIdLookup()
				->localUserFromCentralId(
					$this->params['userId'],
					CentralIdLookup::AUDIENCE_RAW
				);
			if ( $user ) {
				return $services->getUserFactory()->newFromUserIdentity( $user );
			}
		}

		return MassMessage::getMessengerUser();
	}

	/**
	 * Checks whether the target page is in an opt-out category.
	 *
	 * @param Title $title
	 * @return bool
	 */
	public function isOptedOut( Title $title ) {
		$wikipage = WikiPage::factory( $title );
		$categories = $wikipage->getCategories();
		$category = Title::makeTitle(
			NS_CATEGORY,
			wfMessage( 'massmessage-optout-category' )->inContentLanguage()->text()
		);
		foreach ( $categories as $cat ) {
			if ( $category->equals( $cat ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Normalizes the title according to $wgNamespacesToConvert and $wgNamespacesToPostIn.
	 *
	 * @param Title $title
	 * @return Title|null null if we shouldn't post on that title
	 */
	protected function normalizeTitle( Title $title ) {
		global $wgNamespacesToPostIn, $wgNamespacesToConvert;
		if ( isset( $wgNamespacesToConvert[$title->getNamespace()] ) ) {
			$title = Title::makeTitle( $wgNamespacesToConvert[$title->getNamespace()], $title->getText() );
		}
		$title = UrlHelper::followRedirect( $title ) ?: $title; // Try to follow redirects
		if ( !$title->isTalkPage() && !in_array( $title->getNamespace(), $wgNamespacesToPostIn ) ) {
			$this->logLocalSkip( 'skipbadns' );
			$title = null;
		}

		return $title;
	}

	/**
	 * Log any skips on the target site
	 *
	 * @param string $reason log subtype
	 */
	protected function logLocalSkip( $reason ) {
		$logEntry = new ManualLogEntry( 'massmessage', $reason );
		$logEntry->setPerformer( $this->getUser() );
		$logEntry->setTarget( $this->title );
		$logEntry->setParameters( [
			'4::subject' => $this->params['subject']
		] );

		$logid = $logEntry->insert();
		$logEntry->publish( $logid );
	}

	/**
	 * Log any message failures on the target site.
	 *
	 * @param string $reason
	 */
	protected function logLocalFailure( $reason ) {
		$logEntry = new ManualLogEntry( 'massmessage', 'failure' );
		$logEntry->setPerformer( $this->getUser() );
		$logEntry->setTarget( $this->title );
		$logEntry->setParameters( [
			'4::subject' => $this->params['subject'],
			'5::reason' => $reason,
		] );

		$logid = $logEntry->insert();
		$logEntry->publish( $logid );

		// stick it in the debug log
		$text = 'Target: ' . $this->title->getPrefixedText();
		$text .= ' Subject: ' . $this->params['subject'];
		$text .= ' Reason: ' . $reason;
		$text .= ' Origin Wiki: ' . ( $this->params['originWiki'] ?? 'N/A' );
		wfDebugLog( 'MassMessage', $text );
	}

	/**
	 * Send a message to a user.
	 * Modified from the TranslationNotification extension.
	 *
	 * @return bool
	 */
	protected function sendMessage() {
		$title = $this->normalizeTitle( $this->title );
		if ( $title === null ) {
			return true; // Skip it
		}

		$this->title = $title;

		if ( $this->isOptedOut( $this->title ) ) {
			$this->logLocalSkip( 'skipoptout' );
			return true; // Oh well.
		}

		// If we're sending to a User:/User talk: page, make sure the user exists.
		// Redirects are automatically followed in getLocalTargets
		if ( $title->inNamespaces( NS_USER, NS_USER_TALK ) ) {
			$user = User::newFromName( $title->getRootText() );
			if ( !$user || !$user->getId() ) { // Does not exist
				$this->logLocalSkip( 'skipnouser' );
				return true;
			}
		}

		$stripTildes = self::STRIP_TILDES;
		$methodToCall = null;
		// If the page is using a different discussion system, handle it specially
		if (
			ExtensionRegistry::getInstance()->isLoaded( 'Liquid Threads' )
			&& LqtDispatch::isLqtPage( $title )
		) {
			// This is the same check that LQT uses internally
			$methodToCall = [ $this, 'addLQTThread' ];
		} elseif (
			$title->hasContentModel( 'flow-board' )
			// But it can't be a Topic: page, see bug 71196
			&& defined( 'NS_TOPIC' )
			&& !$title->inNamespace( NS_TOPIC )
		) {
			$methodToCall = [ $this, 'addFlowTopic' ];
		} else {
			$stripTildes = !self::STRIP_TILDES;
			$methodToCall = [ $this, 'editPage' ];
		}

		$pageMessageBuilderResult = $this->getPageMessageDetails();
		if ( $pageMessageBuilderResult && !$pageMessageBuilderResult->isOK() ) {
			$this->logLocalFailure(
				$pageMessageBuilderResult->getResultMessage()->text()
			);
			return true;
		}

		$message = $this->buildMessageText( $pageMessageBuilderResult, $stripTildes );
		$subject = $this->buildSubjectText( $pageMessageBuilderResult );
		return $methodToCall( $message, $subject );
	}

	protected function editPage( string $text, string $subject ) {
		$user = $this->getUser();

		$params = [
			'action' => 'edit',
			'title' => $this->title->getPrefixedText(),
			'section' => 'new',
			'summary' => $subject,
			'text' => $text,
			'notminor' => true,
			'token' => $user->getEditToken()
		];

		if ( $this->title->inNamespace( NS_USER_TALK ) ) {
			$params['bot'] = true;
		}

		$result = $this->makeAPIRequest( $params, $user );
		if ( $result ) {
			// Apply change tag if the edit succeeded
			$resultData = $result->getResultData();
			if ( !isset( $resultData['edit']['result'] )
				|| $resultData['edit']['result'] !== 'Success'
			) {
				// job should retry the edit
				return false;
			}
			if ( !isset( $resultData['edit']['nochange'] )
				&& $resultData['edit']['newrevid']
			) {
				$revId = $resultData['edit']['newrevid'];
				DeferredUpdates::addCallableUpdate( static function () use ( $revId ) {
					ChangeTags::addTags( 'massmessage-delivery', null, $revId, null );
				} );
			}
			return true;
		}
		return false;
	}

	protected function addLQTThread( string $text, string $subject ) {
		$user = $this->getUser();

		$params = [
			'action' => 'threadaction',
			'threadaction' => 'newthread',
			'talkpage' => $this->title,
			'subject' => $subject,
			'text' => $text,
			'token' => $user->getEditToken()
		]; // LQT will automatically mark the edit as bot if we're a bot

		return (bool)$this->makeAPIRequest( $params, $user );
	}

	protected function addFlowTopic( string $text, string $subject ) {
		$user = $this->getUser();

		$params = [
			'action' => 'flow',
			'page' => $this->title->getPrefixedText(),
			'submodule' => 'new-topic',
			'nttopic' => $subject,
			'ntcontent' => $text,
			'token' => $user->getEditToken(),
		];

		return (bool)$this->makeAPIRequest( $params, $user );
	}

	/**
	 * Merge page message passed as message, wrap it in necessary HTML tags / attributes and
	 * add some stuff to the end of the message.
	 *
	 * @param PageMessageBuilderResult|null $pageDetails Details of the page being sent as a message
	 * @param bool $stripTildes Whether to strip trailing '~~~~'
	 * @return string
	 */
	private function buildMessageText( ?PageMessageBuilderResult $pageDetails, bool $stripTildes = false ): string {
		$text = rtrim( $this->params['message'] );

		if ( $stripTildes === self::STRIP_TILDES
			&& substr( $text, -4 ) === '~~~~'
			&& substr( $text, -5 ) !== '~~~~~'
		) {
			$text = substr( $text, 0, -4 );
		}

		$pageMessage = $pageDetails ? $pageDetails->getPageMessage() : null;
		$targetLanguage = $this->title->getPageLanguage();

		$text = MassMessage::composeFullMessage(
			$text,
			$pageMessage,
			$targetLanguage,
			$this->params['comment']
		);

		return $text;
	}

	/**
	 * Identify which subject to use - one passed via page message or the subject directly.
	 * Also wrap the subject in appropriate HTML tags / attributes
	 *
	 * @param PageMessageBuilderResult|null $pageDetails
	 * @return string
	 */
	private function buildSubjectText( ?PageMessageBuilderResult $pageDetails ): string {
		$subject = rtrim( $this->params['subject'] ?? null );
		$pageSubject = $pageDetails ? $pageDetails->getPageSubject() : null;

		if ( $pageSubject ) {
			$finalSubject = MassMessage::wrapBasedOnLanguage(
				$pageSubject,
				$this->title->getPageLanguage()
			);

			return $finalSubject;
		}

		return $subject ?? '';
	}

	/**
	 * Construct and make an API request based on the given params and return the results.
	 *
	 * @param array $params
	 * @param User $ourUser
	 * @return \ApiResult|bool
	 */
	protected function makeAPIRequest( array $params, User $ourUser ) {
		// phpcs:ignore MediaWiki.Usage.DeprecatedGlobalVariables.Deprecated$wgUser
		global $wgHooks, $wgUser, $wgRequest;

		// Add our hook functions to make the MassMessage user IP block-exempt and email confirmed.
		// Done here so that it's not unnecessarily called on every page load.
		$lock = MediaWikiServices::getInstance()->getPermissionManager()->addTemporaryUserRights(
			$ourUser, [ 'ipblock-exempt' ]
		);
		$wgHooks['EmailConfirmed'][] = MassMessageHooks::class . '::onEmailConfirmed';

		$oldRequest = $wgRequest;
		$oldUser = $wgUser;

		$wgRequest = new DerivativeRequest(
			$wgRequest,
			$params,
			true // was posted?
		);
		// New user objects will use $wgRequest, so we set that
		// to our DerivativeRequest, so we don't run into any issues.
		$wgUser = $ourUser;
		$context = RequestContext::getMain();
		// All further internal API requests will use the main
		// RequestContext, so setting it here will fix it for
		// all other internal uses, like how LQT does
		$oldCUser = $context->getUser();
		$oldCRequest = $context->getRequest();
		$context->setUser( $wgUser );
		$context->setRequest( $wgRequest );

		$api = new ApiMain(
			$wgRequest,
			true // enable write?
		);
		try {
			$attemptCount = 0;
			while ( true ) {
				try {
					$api->execute();
					break; // Continue after the while block if the API request succeeds
				} catch ( ApiUsageException $e ) {
					$attemptCount++;
					$isEditConflict = false;
					foreach ( $e->getStatusValue()->getErrors() as $error ) {
						if ( ApiMessage::create( $error )->getApiCode() === 'editconflict' ) {
							$isEditConflict = true;
							break;
						}
					}
					// If the failure is not caused by an edit conflict or if there
					// have been too many failures, log the (first) error and continue
					// execution. Otherwise retry the request.
					if ( !$isEditConflict || $attemptCount >= 5 ) {
						foreach ( $e->getStatusValue()->getErrors() as $error ) {
							$this->logLocalFailure( ApiMessage::create( $error )->getApiCode() );
							break;
						}
						return false;
					}
				}
			}
			return $api->getResult();
		} finally {
			// Cleanup all the stuff we polluted
			$context->setUser( $oldCUser );
			$context->setRequest( $oldCRequest );
			$wgUser = $oldUser;
			$wgRequest = $oldRequest;
		}
	}

	/**
	 * Fetch content from the page and the necessary sections
	 *
	 * @return PageMessageBuilderResult|null
	 */
	private function getPageMessageDetails(): ?PageMessageBuilderResult {
		$titleStr = $this->params['pageMessageTitle'] ?? null;
		$isSourceTranslationPage = $this->params['isSourceTranslationPage'] ?? false;
		$pageMessageSection = $this->params['page-message-section'] ?? null;
		$pageSubjectSection = $this->params['page-subject-section'] ?? null;

		if ( !$titleStr ) {
			return null;
		}

		$originWiki = $this->params['originWiki'] ?? WikiMap::getCurrentWikiId();
		$pageMessageBuilder = Services::getInstance()->getPageMessageBuilder();
		if ( $isSourceTranslationPage ) {
			$pageMessageBuilderResult = $pageMessageBuilder->getContentWithFallback(
				$titleStr,
				$this->title->getPageLanguage()->getCode(),
				$this->params['translationPageSourceLanguage'] ?? '',
				$pageMessageSection,
				$pageSubjectSection,
				$originWiki
			);
		} else {
			$pageMessageBuilderResult = $pageMessageBuilder->getContent(
				$titleStr, $pageMessageSection, $pageSubjectSection, $originWiki
			);
		}

		return $pageMessageBuilderResult;
	}
}

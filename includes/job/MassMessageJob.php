<?php

namespace MediaWiki\MassMessage;

use ApiMain;
use ApiMessage;
use ApiUsageException;
use ChangeTags;
use CentralIdLookup;
use DeferredUpdates;
use DerivativeRequest;
use ExtensionRegistry;
use Job;
use LqtDispatch;
use ManualLogEntry;
use MediaWiki\MediaWikiServices;
use RequestContext;
use Title;
use User;
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

	const STRIP_TILDES = true;

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
			$centralIdLookup = CentralIdLookup::factory();
			$user = $centralIdLookup->localUserFromCentralId(
				$this->params['userId'],
				CentralIdLookup::AUDIENCE_RAW
			);
			if ( $user ) {
				return $user;
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
		wfDebugLog( 'massmessage', $text );
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
		if ( $title->getNamespace() === NS_USER || $title->getNamespace() === NS_USER_TALK ) {
			$user = User::newFromName( $title->getRootText() );
			if ( !$user || !$user->getId() ) { // Does not exist
				$this->logLocalSkip( 'skipnouser' );
				return true;
			}
		}

		// If the page is using a different discussion system, handle it specially
		if ( ExtensionRegistry::getInstance()->isLoaded( 'Liquid Threads' ) &&
			LqtDispatch::isLqtPage( $title )
		) {
			// This is the same check that LQT uses internally
			$this->addLQTThread();
		} elseif ( $title->hasContentModel( 'flow-board' )
			// But it can't be a Topic: page, see bug 71196
			&& defined( 'NS_TOPIC' ) && !$title->inNamespace( NS_TOPIC ) ) {
			$this->addFlowTopic();
		} else {
			$this->editPage();
		}

		return true;
	}

	protected function editPage() {
		$user = $this->getUser();
		$params = [
			'action' => 'edit',
			'title' => $this->title->getPrefixedText(),
			'section' => 'new',
			'summary' => $this->params['subject'],
			'text' => $this->makeText(),
			'notminor' => true,
			'token' => $user->getEditToken()
		];

		if ( $this->title->getNamespace() === NS_USER_TALK ) {
			$params['bot'] = true;
		}

		$result = $this->makeAPIRequest( $params );

		// Apply change tag if the edit succeeded
		$resultData = $result->getResultData();
		if ( !array_key_exists( 'error', $resultData ) ) {
			$revId = $resultData['edit']['newrevid'];
			DeferredUpdates::addCallableUpdate( function () use ( $revId ) {
				ChangeTags::addTags( 'massmessage-delivery', null, $revId, null );
			} );
		}
	}

	protected function addLQTThread() {
		$user = $this->getUser();
		$params = [
			'action' => 'threadaction',
			'threadaction' => 'newthread',
			'talkpage' => $this->title,
			'subject' => $this->params['subject'],
			'text' => $this->makeText( self::STRIP_TILDES ),
			'token' => $user->getEditToken()
		]; // LQT will automatically mark the edit as bot if we're a bot

		$this->makeAPIRequest( $params );
	}

	protected function addFlowTopic() {
		$user = $this->getUser();
		$params = [
			'action' => 'flow',
			'page' => $this->title->getPrefixedText(),
			'submodule' => 'new-topic',
			'nttopic' => $this->params['subject'],
			'ntcontent' => $this->makeText( self::STRIP_TILDES ),
			'token' => $user->getEditToken(),
		];

		$this->makeAPIRequest( $params );
	}

	/**
	 * Add some stuff to the end of the message.
	 *
	 * @param bool $stripTildes Whether to strip trailing '~~~~'
	 * @return string
	 */
	protected function makeText( $stripTildes = false ) {
		$text = rtrim( $this->params['message'] );
		if ( $stripTildes === self::STRIP_TILDES
			&& substr( $text, -4 ) === '~~~~'
			&& substr( $text, -5 ) !== '~~~~~'
		) {
			$text = substr( $text, 0, -4 );
		}
		$text .= "\n" . wfMessage( 'massmessage-hidden-comment' )
				->params( $this->params['comment'] )->text();
		return $text;
	}

	/**
	 * Construct and make an API request based on the given params and return the results.
	 *
	 * @param array $params
	 * @return \ApiResult
	 */
	protected function makeAPIRequest( array $params ) {
		global $wgHooks, $wgUser, $wgRequest;

		// Add our hook functions to make the MassMessage user IP block-exempt and email confirmed.
		// Done here so that it's not unnecessarily called on every page load.
		$ourUser = $this->getUser();
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
					break;
				}
			}
		}

		// Cleanup all the stuff we polluted
		$context->setUser( $oldCUser );
		$context->setRequest( $oldCRequest );
		$wgUser = $oldUser;
		$wgRequest = $oldRequest;

		return $api->getResult();
	}
}

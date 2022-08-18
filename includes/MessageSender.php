<?php
declare( strict_types = 1 );

namespace MediaWiki\MassMessage;

use ApiMain;
use ApiMessage;
use ApiResult;
use ApiUsageException;
use ChangeTags;
use DeferredUpdates;
use DerivativeRequest;
use MediaWiki\Permissions\PermissionManager;
use RequestContext;
use Title;
use User;

/**
 * Post messages on target pages
 * @author Abijeet Patro
 * @since 2022.08
 * @license GPL-2.0-or-later
 */
class MessageSender {
	/** @var PermissionManager */
	private $permissionManager;
	/** @var callable|null */
	private $failureCallback;

	public function __construct(
		PermissionManager $permissionManager,
		?callable $failureCallback
	) {
		$this->permissionManager = $permissionManager;
		$this->failureCallback = $failureCallback;
	}

	public function editPage(
		Title $target,
		string $message,
		string $subject,
		User $user
	): bool {
		$params = [
			'action' => 'edit',
			'title' => $target->getPrefixedText(),
			'section' => 'new',
			'summary' => $subject,
			'text' => $message,
			'notminor' => true,
			'token' => $user->getEditToken()
		];

		if ( $target->inNamespace( NS_USER_TALK ) ) {
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
					ChangeTags::addTags( 'massmessage-delivery', null, $revId );
				} );
			}
			return true;
		}
		return false;
	}

	public function addLQTThread(
		Title $target,
		string $message,
		string $subject,
		User $user
	): bool {
		$params = [
			'action' => 'threadaction',
			'threadaction' => 'newthread',
			'talkpage' => $target,
			'subject' => $subject,
			'text' => $message,
			'token' => $user->getEditToken()
		]; // LQT will automatically mark the edit as bot if we're a bot

		return (bool)$this->makeAPIRequest( $params, $user );
	}

	public function addFlowTopic(
		Title $target,
		string $message,
		string $subject,
		User $user
	): bool {
		$params = [
			'action' => 'flow',
			'page' => $target->getPrefixedText(),
			'submodule' => 'new-topic',
			'nttopic' => $subject,
			'ntcontent' => $message,
			'token' => $user->getEditToken(),
		];

		return (bool)$this->makeAPIRequest( $params, $user );
	}

	/**
	 * Construct and make an API request based on the given params and return the results.
	 *
	 * @param array $params
	 * @param User $ourUser
	 * @return ?ApiResult
	 */
	private function makeAPIRequest( array $params, User $ourUser ): ?ApiResult {
		// phpcs:ignore MediaWiki.Usage.DeprecatedGlobalVariables.Deprecated$wgUser
		global $wgHooks, $wgUser, $wgRequest;

		// Add our hook functions to make the MassMessage user IP block-exempt and email confirmed.
		// Done here so that it's not unnecessarily called on every page load.
		$lock = $this->permissionManager->addTemporaryUserRights( $ourUser, [ 'ipblock-exempt' ] );
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
		$context->setUser( $ourUser );
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
							if ( $this->failureCallback ) {
								call_user_func( $this->failureCallback, ApiMessage::create( $error )->getApiCode() );
							}
							break;
						}
						return null;
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
}

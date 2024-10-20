<?php

namespace MediaWiki\MassMessage\Job;

use Job;
use LqtDispatch;
use ManualLogEntry;
use MediaWiki\MassMessage\DedupeHelper;
use MediaWiki\MassMessage\Job\Hooks\HookRunner;
use MediaWiki\MassMessage\LanguageAwareText;
use MediaWiki\MassMessage\MassMessage;
use MediaWiki\MassMessage\MessageBuilder;
use MediaWiki\MassMessage\MessageSender;
use MediaWiki\MassMessage\PageMessage\PageMessageBuilderResult;
use MediaWiki\MassMessage\Services;
use MediaWiki\MassMessage\UrlHelper;
use MediaWiki\MediaWikiServices;
use MediaWiki\Registration\ExtensionRegistry;
use MediaWiki\Title\Title;
use MediaWiki\User\CentralId\CentralIdLookup;
use MediaWiki\User\User;
use MediaWiki\WikiMap\WikiMap;

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
	/**
	 * @var bool Whether to use sender account (if possible)
	 * TODO: Expose this as a configurable option (T71954)
	 */
	private $useSenderUser = false;
	/** @var MessageSender|null */
	private $messageSender;
	/** @var HookRunner */
	private $hookRunner;

	/**
	 * @param Title $title
	 * @param array $params
	 */
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

		$this->hookRunner = new HookRunner(
			MediaWikiServices::getInstance()->getHookContainer()
		);
	}

	/**
	 * Execute the job.
	 *
	 * @return bool
	 */
	public function run() {
		$this->messageSender = new MessageSender(
			MediaWikiServices::getInstance()->getPermissionManager(),
			function ( string $msg ) {
				$this->logLocalFailure( $msg );
			}
		);

		$status = $this->sendMessage();
		if ( !$status ) {
			$this->setLastError( 'There was an error while sending the message.' );
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
		$wikipage = MediaWikiServices::getInstance()->getWikiPageFactory()->newFromTitle( $title );
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
	 * Normalizes the title according to $wgNamespacesToConvert, $wgNamespacesToPostIn
	 * and $wgAllowlistedMassMessageTargets.
	 *
	 * @param Title $title
	 * @return Title|null null if we shouldn't post on that title
	 */
	protected function normalizeTitle( Title $title ) {
		$mainConfig = MediaWikiServices::getInstance()->getMainConfig();
		$namespacesToConvert = $mainConfig->get( 'NamespacesToConvert' );
		if ( isset( $namespacesToConvert[$title->getNamespace()] ) ) {
			$title = Title::makeTitle( $namespacesToConvert[$title->getNamespace()], $title->getText() );
		}
		// Try to follow redirects
		$title = UrlHelper::followRedirect( $title ) ?: $title;
		if (
			!$title->isTalkPage() &&
			!in_array( $title->getNamespace(), $mainConfig->get( 'NamespacesToPostIn' ) ) &&
			!in_array( $title->getId(), $mainConfig->get( 'AllowlistedMassMessageTargets' ) )
		) {
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
		$this->logToDebugLog( $reason );
	}

	/**
	 * Log failures due to an invalid subject section from the source page.
	 */
	protected function logLocalSubjectSectionFailure(): void {
		$logEntry = new ManualLogEntry( 'massmessage', 'failure-section' );
		$logEntry->setPerformer( $this->getUser() );
		$logEntry->setTarget( $this->title );
		$logEntry->setParameters( [
			'4::subject' => $this->params['subject'],
			'5::subject_section' => $this->params[ 'page-subject-section' ] ?? null,
			'6::source_page' => $this->params['pageMessageTitle'] ?? null,
		] );

		$logid = $logEntry->insert();
		$logEntry->publish( $logid );
	}

	/**
	 * Log metadata about a delivery to the debug log with the given reason.
	 *
	 * @param string $reason
	 */
	protected function logToDebugLog( $reason ) {
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
	protected function sendMessage(): bool {
		$title = $this->normalizeTitle( $this->title );
		if ( $title === null ) {
			// Skip it
			return true;
		}

		$this->title = $title;

		if ( !$this->canDeliverMessage( $title ) ) {
			return true;
		}

		$pageMessageBuilderResult = $this->getPageMessageDetails();
		if ( $pageMessageBuilderResult && !$pageMessageBuilderResult->isOK() ) {
			$this->logLocalFailure(
				$pageMessageBuilderResult->getResultMessage()->text()
			);
			return true;
		}

		$subject = $this->params['subject'] ?? '';
		$message = $this->params['message'] ?? '';
		$pageSubject = $pageMessageBuilderResult ? $pageMessageBuilderResult->getPageSubject() : null;
		$pageMessage = $pageMessageBuilderResult ? $pageMessageBuilderResult->getPageMessage() : null;

		$dedupeHash = DedupeHelper::getDedupeHash( $subject, $message, $pageSubject, $pageMessage );
		if ( DedupeHelper::hasRecentlyDeliveredDuplicate( $this->title, $dedupeHash ) ) {
			$this->logToDebugLog( 'Delivery skipped because it is a duplicate of a recently delivered message.' );
			return true;
		}

		return $this->deliverMessage(
			$title,
			$subject,
			$message,
			$pageSubject,
			$pageMessage,
			$this->params['comment'],
			$dedupeHash,
		);
	}

	/**
	 * @param string $text
	 * @param string $subject
	 * @param string $dedupeHash
	 * @return bool
	 */
	protected function editPage( string $text, string $subject, string $dedupeHash ): bool {
		return $this->messageSender->editPage( $this->title, $text, $subject, $this->getUser(), $dedupeHash );
	}

	/**
	 * @param string $text
	 * @param string $subject
	 * @return bool
	 */
	protected function addLQTThread( string $text, string $subject ): bool {
		return $this->messageSender->addLQTThread( $this->title, $text, $subject, $this->getUser() );
	}

	/**
	 * @param string $text
	 * @param string $subject
	 * @return bool
	 */
	protected function addFlowTopic( string $text, string $subject ): bool {
		return $this->messageSender->addFlowTopic( $this->title, $text, $subject, $this->getUser() );
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

	/**
	 * Check if it's OK to deliver the message to the client
	 * @param Title $title
	 * @return bool
	 */
	private function canDeliverMessage( Title $title ): bool {
		if ( $this->isOptedOut( $this->title ) ) {
			$this->logLocalSkip( 'skipoptout' );
			// Oh well.
			return false;
		}

		// If we're sending to a User:/User talk: page, make sure the user exists.
		// Redirects are automatically followed in getLocalTargets
		if (
			$title->inNamespaces( NS_USER, NS_USER_TALK ) &&
			!in_array(
				$title->getId(),
				MediaWikiServices::getInstance()->getMainConfig()->get( 'AllowlistedMassMessageTargets' )
			)
		) {
			$user = User::newFromName( $title->getRootText() );
			if ( !$user || !$user->isNamed() ) {
				// Don't send to anonymous and temporary users
				$this->logLocalSkip( 'skipnouser' );
				return false;
			}
		}

		return true;
	}

	/**
	 * Deliver the message to the target page after making tweaks to the message based on
	 * the discussion system of target page.
	 * @param Title $targetPage
	 * @param string $subject
	 * @param string $message
	 * @param LanguageAwareText|null $pageSubject
	 * @param LanguageAwareText|null $pageMessage
	 * @param string[] $comment
	 * @param string $dedupeHash
	 * @return bool
	 */
	private function deliverMessage(
		Title $targetPage,
		string $subject,
		string $message,
		?LanguageAwareText $pageSubject,
		?LanguageAwareText $pageMessage,
		array $comment,
		string $dedupeHash
	): bool {
		$targetLanguage = $targetPage->getPageLanguage();
		$messageBuilder = new MessageBuilder();

		$isLqtThreads = ExtensionRegistry::getInstance()->isLoaded( 'Liquid Threads' )
			&& LqtDispatch::isLqtPage( $targetPage );
		$isStructuredDiscussion = $targetPage->hasContentModel( 'flow-board' )
			// But it can't be a Topic: page, see bug 71196
			&& defined( 'NS_TOPIC' )
			&& !$targetPage->inNamespace( NS_TOPIC );

		$failureCallback = function ( $msg ) {
			$this->logLocalFailure( $msg );
		};
		// Allow hooks to override processing
		if ( !$this->hookRunner->onMassMessageJobBeforeMessageSent(
			$failureCallback,
			$targetPage,
			$subject,
			$message,
			$pageSubject,
			$pageMessage,
			$comment
		) ) {
			// Hook returning false means that the hook handler
			// sent the message and is asking us to not send the message ourselves.
			// We still return true since the hook did successfully send the message.
			return true;
		}

		// If the page is using a different discussion system, handle it specially
		if ( $isLqtThreads || $isStructuredDiscussion ) {
			$subject = $messageBuilder->buildPlaintextSubject( $subject, $pageSubject );

			if ( $subject === '' ) {
				$this->logLocalSubjectSectionFailure();
				return false;
			}

			$message = $messageBuilder->buildMessage(
				$messageBuilder->stripTildes( $message ),
				$pageMessage,
				$targetLanguage,
				$comment
			);

			if ( $isLqtThreads ) {
				return $this->addLQTThread( $message, $subject );
			} else {
				return $this->addFlowTopic( $message, $subject );
			}
		}
		$subject = $messageBuilder->buildSubject( $subject, $pageSubject, $targetLanguage );
		$message = $messageBuilder->buildMessage( $message, $pageMessage, $targetLanguage, $comment );

		if ( $subject === '' ) {
			$this->logLocalSubjectSectionFailure();
			return false;
		}
		return $this->editPage( $message, $subject, $dedupeHash );
	}
}

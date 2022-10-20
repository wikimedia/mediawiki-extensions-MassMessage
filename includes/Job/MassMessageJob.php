<?php

namespace MediaWiki\MassMessage\Job;

use CentralIdLookup;
use ExtensionRegistry;
use Job;
use LqtDispatch;
use ManualLogEntry;
use MediaWiki\MassMessage\LanguageAwareText;
use MediaWiki\MassMessage\MassMessage;
use MediaWiki\MassMessage\MessageBuilder;
use MediaWiki\MassMessage\MessageSender;
use MediaWiki\MassMessage\PageMessage\PageMessageBuilderResult;
use MediaWiki\MassMessage\Services;
use MediaWiki\MassMessage\UrlHelper;
use MediaWiki\MediaWikiServices;
use Title;
use User;
use WikiMap;

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
	protected function sendMessage(): bool {
		$title = $this->normalizeTitle( $this->title );
		if ( $title === null ) {
			return true; // Skip it
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

		return $this->deliverMessage(
			$title,
			$this->params['subject'] ?? '',
			$this->params['message'] ?? '',
			$pageMessageBuilderResult ? $pageMessageBuilderResult->getPageSubject() : null,
			$pageMessageBuilderResult ? $pageMessageBuilderResult->getPageMessage() : null,
			$this->params['comment']
		);
	}

	/**
	 * @param string $text
	 * @param string $subject
	 * @return bool
	 */
	protected function editPage( string $text, string $subject ): bool {
		return $this->messageSender->editPage( $this->title, $text, $subject, $this->getUser() );
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
			return false; // Oh well.
		}

		// If we're sending to a User:/User talk: page, make sure the user exists.
		// Redirects are automatically followed in getLocalTargets
		if ( $title->inNamespaces( NS_USER, NS_USER_TALK ) ) {
			$user = User::newFromName( $title->getRootText() );
			if ( !$user || !$user->getId() ) { // Does not exist
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
	 * @param array $comment
	 * @return bool
	 */
	private function deliverMessage(
		Title $targetPage,
		string $subject,
		string $message,
		?LanguageAwareText $pageSubject,
		?LanguageAwareText $pageMessage,
		array $comment
	): bool {
		$targetLanguage = $targetPage->getPageLanguage();
		$messageBuilder = new MessageBuilder();

		$isLqtThreads = ExtensionRegistry::getInstance()->isLoaded( 'Liquid Threads' )
			&& LqtDispatch::isLqtPage( $targetPage );
		$isStructuredDiscussion = $targetPage->hasContentModel( 'flow-board' )
			// But it can't be a Topic: page, see bug 71196
			&& defined( 'NS_TOPIC' )
			&& !$targetPage->inNamespace( NS_TOPIC );

		// If the page is using a different discussion system, handle it specially
		if ( $isLqtThreads || $isStructuredDiscussion ) {
			$subject = $messageBuilder->buildPlaintextSubject( $subject, $pageSubject );
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
		return $this->editPage( $message, $subject );
	}
}

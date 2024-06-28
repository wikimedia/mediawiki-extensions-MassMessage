<?php

namespace MediaWiki\MassMessage\Job\Hooks;

use MediaWiki\MassMessage\LanguageAwareText;
use MediaWiki\Title\Title;

interface MassMessageJobBeforeMessageSentHook {
	/**
	 * Hook runner for `MassMessageJobBeforeMessageSent` hook
	 *
	 * Allows overriding how mass messages are sent. For example,
	 * if you had a custom discussion page extension, you might want
	 * to use this to change how messages are posted on user talk pages.
	 *
	 * Generally you would use the MessageBuilder class to actually
	 * construct the text of the message.
	 *
	 * Returning false will prevent MassMessage from sending the message. If
	 * your hook sent the message itself, you should return false to prevent
	 * MassMessage from trying to send the same message again.
	 *
	 * @param callable $failureCallback proxy for MassMessageJob::logLocalFailure
	 * @param Title $targetPage Page message is being sent to
	 * @param string $subject
	 * @param string $message
	 * @param ?LanguageAwareText $pageSubject
	 * @param ?LanguageAwareText $pageMessage
	 * @param string[] $comment Parameters to 'massmessage-hidden-comment' message
	 * @return bool|void True to have MassMessage send the message normally, false to not
	 */
	public function onMassMessageJobBeforeMessageSent(
		callable $failureCallback,
		Title $targetPage,
		string $subject,
		string $message,
		?LanguageAwareText $pageSubject,
		?LanguageAwareText $pageMessage,
		array $comment
	);
}

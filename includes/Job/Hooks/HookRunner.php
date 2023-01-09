<?php

namespace MediaWiki\MassMessage\Job\Hooks;

use MediaWiki\HookContainer\HookContainer;
use MediaWiki\MassMessage\LanguageAwareText;
use Title;

class HookRunner implements MassMessageJobBeforeSentHook {

	/** @var HookContainer */
	private $hookContainer;

	/**
	 * @param HookContainer $hookContainer
	 */
	public function __construct( HookContainer $hookContainer ) {
		$this->hookContainer = $hookContainer;
	}

	/**
	 * @inheritDoc
	 */
	public function onMassMessageJobBeforeMessageSent(
		callable $failureCallback,
		Title $targetPage,
		string $subject,
		string $message,
		?LanguageAwareText $pageSubject,
		?LanguageAwareText $pageMessage,
		array $comment
	) {
		return $this->hookContainer->run(
			'MassMessageJobBeforeMessageSent',
			[ $failureCallback, $targetPage, $subject, $message, $pageSubject, $pageMessage, $comment ]
		);
	}
}

<?php
declare( strict_types = 1 );

namespace MediaWiki\MassMessage\PageMessage;

use MediaWiki\MassMessage\LanguageAwareText;
use MediaWiki\Message\Message;
use MediaWiki\Status\Status;

/**
 * Returned by PageMessageBuilder class: getContent and getContentWithFallback method
 * to represent a page / section to be sent as message / subject
 * @author Abijeet Patro
 * @since 2022.01
 * @license GPL-2.0-or-later
 */
class PageMessageBuilderResult {
	public function __construct(
		private readonly Status $status,
		private readonly ?LanguageAwareText $pageMessage = null,
		private readonly ?LanguageAwareText $pageSubject = null,
	) {
	}

	public function getStatus(): Status {
		return $this->status;
	}

	public function getPageMessage(): ?LanguageAwareText {
		return $this->pageMessage;
	}

	public function getPageSubject(): ?LanguageAwareText {
		return $this->pageSubject;
	}

	public function isOK(): bool {
		return $this->status->isOK();
	}

	public function getResultMessage(): Message {
		return $this->status->getMessage();
	}
}

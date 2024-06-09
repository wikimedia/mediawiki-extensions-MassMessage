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
	/** @var Status */
	private $status;
	/** @var LanguageAwareText|null */
	private $pageMessage;
	/** @var LanguageAwareText|null */
	private $pageSubject;

	/**
	 * @param Status $status
	 * @param LanguageAwareText|null $pageMessage
	 * @param LanguageAwareText|null $pageSubject
	 */
	public function __construct(
		Status $status,
		?LanguageAwareText $pageMessage = null,
		?LanguageAwareText $pageSubject = null
	) {
		$this->status = $status;
		$this->pageMessage = $pageMessage;
		$this->pageSubject = $pageSubject;
	}

	/**
	 * @return Status
	 */
	public function getStatus(): Status {
		return $this->status;
	}

	/**
	 * @return LanguageAwareText|null
	 */
	public function getPageMessage(): ?LanguageAwareText {
		return $this->pageMessage;
	}

	/**
	 * @return LanguageAwareText|null
	 */
	public function getPageSubject(): ?LanguageAwareText {
		return $this->pageSubject;
	}

	/**
	 * @return bool
	 */
	public function isOK(): bool {
		return $this->status->isOK();
	}

	/**
	 * @return Message
	 */
	public function getResultMessage(): Message {
		return $this->status->getMessage();
	}
}

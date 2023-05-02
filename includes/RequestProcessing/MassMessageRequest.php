<?php
declare( strict_types = 1 );

namespace MediaWiki\MassMessage\RequestProcessing;

use MediaWiki\Title\Title;

/**
 * Represents a request submitted by user for sending a mass message
 * @author Abijeet Patro
 * @since 2021.12
 * @license GPL-2.0-or-later
 */
class MassMessageRequest {
	/** @var Title */
	private $spamList;
	/** @var string */
	private $subject;
	/** @var string */
	private $pageMessage;
	/** @var string */
	private $pageMessageSection;
	/** @var string */
	private $pageSubjectSection;
	/** @var string */
	private $message;
	/** @var string[] */
	private $comment;

	/**
	 * @param Title $spamList
	 * @param string $subject
	 * @param string $pageMessage
	 * @param string $pageMessageSection
	 * @param string $pageSubjectSection
	 * @param string $message
	 * @param string[] $comment
	 */
	public function __construct(
		Title $spamList,
		string $subject,
		string $pageMessage,
		string $pageMessageSection,
		string $pageSubjectSection,
		string $message,
		array $comment
	) {
		$this->spamList = $spamList;
		$this->subject = $subject;
		$this->pageMessage = $pageMessage;
		$this->pageMessageSection = $pageMessageSection;
		$this->pageSubjectSection = $pageSubjectSection;
		$this->message = $message;
		$this->comment = $comment;
	}

	/** @return Title */
	public function getSpamList(): Title {
		return $this->spamList;
	}

	/** @return string */
	public function getSubject(): string {
		return $this->subject;
	}

	/** @return string */
	public function getPageMessage(): string {
		return $this->pageMessage;
	}

	/** @return string */
	public function getPageMessageSection(): string {
		return $this->pageMessageSection;
	}

	/** @return string */
	public function getPageSubjectSection(): string {
		return $this->pageSubjectSection;
	}

	/** @return string */
	public function getMessage(): string {
		return $this->message;
	}

	/** @return string[] */
	public function getComment(): array {
		return $this->comment;
	}

	/** @return bool */
	public function hasPageMessage(): bool {
		return $this->pageMessage !== '';
	}

	/** @return bool */
	public function hasMessage(): bool {
		return $this->message !== '';
	}

	/** @return array */
	public function getSerializedData(): array {
		return [
			'spamList' => $this->getSpamList()->getPrefixedText(),
			'subject' => $this->getSubject(),
			'page-message' => $this->getPageMessage(),
			'page-message-section' => $this->getPageMessageSection(),
			'page-subject-section' => $this->getPageSubjectSection(),
			'message' => $this->getMessage(),
			'comment' => $this->getComment()
		];
	}
}

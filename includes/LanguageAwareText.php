<?php
declare( strict_types = 1 );

namespace MediaWiki\MassMessage;

/*
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 */
class LanguageAwareText {
	/** @var string */
	private $wikitext;
	/** @var string */
	private $languageCode;
	/** @var string */
	private $languageDirection;

	/**
	 * @param string $wikitext
	 * @param string $languageCode
	 * @param string $languageDirection
	 */
	public function __construct(
		string $wikitext,
		string $languageCode,
		string $languageDirection
	) {
		$this->wikitext = $wikitext;
		$this->languageCode = $languageCode;
		$this->languageDirection = $languageDirection;
	}

	/** @return string */
	public function getWikitext(): string {
		return $this->wikitext;
	}

	/** @return string */
	public function getLanguageCode(): string {
		return $this->languageCode;
	}

	/** @return string */
	public function getLanguageDirection(): string {
		return $this->languageDirection;
	}
}

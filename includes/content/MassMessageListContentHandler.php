<?php

class MassMessageListContentHandler extends TextContentHandler {

	/**
	 * @param string $modelId
	 */
	public function __construct( $modelId = 'MassMessageListContent' ) {
		parent::__construct( $modelId, array( CONTENT_FORMAT_JSON ) );
	}

	/**
	 * @param string $text
	 * @param string $format
	 * @return MassMessageListContent
	 */
	public function unserializeContent( $text, $format = null ) {
		$this->checkFormat( $format );
		$content = new MassMessageListContent( $text );
		if ( !$content->isValid() ) {
			throw new MWContentSerializationException( 'The delivery list content is invalid.' );
		}
		return $content;
	}

	/**
	 * @return MassMessageListContent
	 */
	public function makeEmptyContent() {
		return new MassMessageListContent( '{"description":"","targets":[]}' );
	}

	/**
	 * Edit a delivery list via the edit API
	 * @param Title $title
	 * @param string $description
	 * @param array $targets
	 * @param string $summary Message key for edit summary
	 * @param IContextSource $context The calling context
	 * @return Status
	 */
	public static function edit( Title $title, $description, $targets, $summary,
		IContextSource $context
	) {
		$jsonText = FormatJson::encode(
			array( 'description' => $description, 'targets' => $targets )
		);
		if ( $jsonText === null ) {
			return Status::newFatal( 'massmessage-content-tojsonerror' );
		}

		$request = new DerivativeRequest(
			$context->getRequest(),
			array(
				'action' => 'edit',
				'title' => $title->getFullText(),
				'contentmodel' => 'MassMessageListContent',
				'text' => $jsonText,
				'summary' => $context->msg( $summary )->inContentLanguage()->plain(),
				'token' => $context->getUser()->getEditToken(),
			),
			true // Treat data as POSTed
		);

		try {
			$api = new ApiMain( $request, true );
			$api->execute();
		} catch ( UsageException $e ) {
			return Status::newFatal( $context->msg( 'massmessage-content-apierror',
				$e->getCodeString() ) );
		}
		return Status::newGood();
	}

	/**
	 * Deduplicate and sort a target array
	 * @param array $targets
	 * @return array
	 */
	public static function normalizeTargetArray( $targets ) {
		$targets = array_unique( $targets, SORT_REGULAR );
		usort( $targets, 'self::compareTargets' );
		return $targets;
	}

	/**
	 * Compare two targets for ordering
	 * @param array $a
	 * @paran array $b
	 * @return int
	 */
	public static function compareTargets( $a, $b ) {
		if ( !array_key_exists( 'site', $a ) && array_key_exists( 'site', $b ) ) {
			return -1;
		} else if ( array_key_exists( 'site', $a ) && !array_key_exists( 'site', $b ) ) {
			return 1;
		} else if ( array_key_exists( 'site', $a ) && array_key_exists( 'site', $b )
			&& $a['site'] !== $b['site']
		) {
			return strcmp( $a['site'], $b['site'] );
		} else {
			return strcmp( $a['title'], $b['title'] );
		}
	}

	/**
	 * Helper function to extract and validate title and site (if specified) from a target string
	 * Returns null if the target string doesn't specify a valid target
	 * @param string $target
	 * @return array|null
	 */
	public static function extractTarget( $target ) {
		global $wgCanonicalServer, $wgAllowGlobalMessaging;

		$target = trim( $target );
		$delimiterPos = strrpos( $target, '@' );
		if ( $delimiterPos !== false && $delimiterPos < strlen( $target ) ) {
			$titleText = substr( $target, 0, $delimiterPos );
			$site = strtolower( substr( $target, $delimiterPos+1 ) );
		} else {
			$titleText = $target;
			$site = null;
		}

		$title = Title::newFromText( $titleText );
		if ( !$title ) {
			return null; // Invalid title
		}
		$titleText = $title->getPrefixedText(); // Use the canonical form.

		if ( $site ) {
			if ( $site === MassMessage::getBaseUrl( $wgCanonicalServer ) ) {
				$site = null; // Do not return site for the local wiki.
			} else {
				$wiki = MassMessage::getDBName( $site );
				if ( $wiki === null || !$wgAllowGlobalMessaging && $wiki !== wfWikiID() ) {
					return null; // Invalid site
				}
			}
		}

		if ( $site ) {
			return array( 'title' => $titleText, 'site' => $site );
		} else {
			return array( 'title' => $titleText );
		}
	}
}

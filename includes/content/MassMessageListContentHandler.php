<?php

class MassMessageListContentHandler extends JSONContentHandler {

	/**
	 * @param string $modelId
	 */
	public function __construct( $modelId = 'MassMessageListContent' ) {
		parent::__construct( $modelId );
	}

	/**
	 * @param string $text
	 * @param string $format
	 * @return MassMessageListContent
	 * @throws MWContentSerializationException
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
	 * @return string
	 */
	protected function getContentClass() {
		return 'MassMessageListContent';
	}

	/**
	 * @return string
	 */
	protected function getDiffEngineClass() {
		return 'MassMessageListDiffEngine';
	}

	/**
	 * @return bool
	 */
	public function isParserCacheSupported() {
		return true;
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
			return Status::newFatal( 'massmessage-ch-tojsonerror' );
		}

		// Ensure that a valid context is provided to the API in unit tests
		$der = new DerivativeContext( $context );
		$request = new DerivativeRequest(
			$context->getRequest(),
			array(
				'action' => 'edit',
				'title' => $title->getFullText(),
				'contentmodel' => 'MassMessageListContent',
				'text' => $jsonText,
				'summary' => $summary,
				'token' => $context->getUser()->getEditToken(),
			),
			true // Treat data as POSTed
		);
		$der->setRequest( $request );

		try {
			$api = new ApiMain( $der, true );
			$api->execute();
		} catch ( UsageException $e ) {
			return Status::newFatal( $context->msg( 'massmessage-ch-apierror',
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
		} elseif ( array_key_exists( 'site', $a ) && !array_key_exists( 'site', $b ) ) {
			return 1;
		} elseif ( array_key_exists( 'site', $a ) && array_key_exists( 'site', $b )
			&& $a['site'] !== $b['site']
		) {
			return strcmp( $a['site'], $b['site'] );
		} else {
			return strcmp( $a['title'], $b['title'] );
		}
	}

	/**
	 * Helper function to extract and validate title and site (if specified) from a target string
	 * @param string $target
	 * @return array Contains an 'errors' key for an array of errors if the string is invalid
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

		$result = array();

		$title = Title::newFromText( $titleText );
		if ( !$title
			|| $title->getText() === ''
			|| !$title->canExist()
			|| $title->isExternal()
		) {
			$result['errors'] = array( 'invalidtitle' );
		} else {
			$result['title'] = $title->getPrefixedText(); // Use the canonical form.
		}

		if ( $site !== null && $site !== MassMessage::getBaseUrl( $wgCanonicalServer ) ) {
			if ( !$wgAllowGlobalMessaging || MassMessage::getDBName( $site ) === null ) {
				if ( array_key_exists( 'errors', $result ) ) {
					$result['errors'][] = 'invalidsite';
				} else {
					$result['errors'] = array( 'invalidsite' );
				}
			} else {
				$result['site'] = $site;
			}
		}

		return $result;
	}
}

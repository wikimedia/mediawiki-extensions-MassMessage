<?php

class SpecialEditMassMessageList extends FormSpecialPage {

	/**
	 * @var Title|null
	 */
	protected $title;

	/**
	 * The message key for the error encountered while parsing the title, if any.
	 * @var string|null
	 */
	protected $errorMsgKey;

	public function __construct() {
		parent::__construct( 'EditMassMessageList' );
	}

	/**
	 * @param string $par
	 */
	protected function setParameter( $par ) {
		if ( $par === null || $par === '' ) {
			$this->errorMsgKey = 'massmessage-edit-invalidtitle';
		} else {
			$title = Title::newFromText( $par );

			if ( !$title
				|| !$title->exists()
				|| !$title->hasContentModel( 'MassMessageListContent' )
			) {
				$this->errorMsgKey = 'massmessage-edit-invalidtitle';
			} else if ( !$title->userCan( 'edit' ) ) {
				$this->errorMsgKey = 'massmessage-edit-nopermission';
			} else {
				$this->title = $title;
			}
		}
	}

	/**
	 * @return array
	 */
	protected function getFormFields() {

		// Show a hidden empty form if the title is invalid.
		if ( !$this->title ) {
			return array();
		}

		$content = Revision::newFromTitle( $this->title )->getContent();
		$description = $content->getDescription();
		$targets = $content->getTargets();

		return array(
			'title' => array(
				'type' => 'text',
				'disabled' => true,
				'default' => $this->title->getPrefixedText(),
				'label-message' => 'massmessage-edit-title',
			),
			'description' => array(
				'type' => 'textarea',
				'rows' => 5,
				'default' => ( $description !== null ) ? $description : '',
				'label-message' => 'massmessage-edit-description',
			),
			'content' => array(
				'type' => 'textarea',
				'default' => ( $targets !== null ) ? self::parseTargets( $targets ) : '',
				'label-message' => 'massmessage-edit-content',
			),
		);
	}

	/**
	 * Hide the form if the title is invalid.
	 * @param HTMLForm $form
	 */
	protected function alterForm( HTMLForm $form ) {
		if ( !$this->title ) {
			$form->setWrapperLegend( false );
			$form->suppressDefaultSubmit( true );
		}
	}

	/**
	 * @return string
	 */
	protected function preText() {
		if ( $this->title ) {
			$msgKey = 'massmessage-edit-header';
		} else {
			$msgKey = $this->errorMsgKey;
		}
		return '<p>' . $this->msg( $msgKey )->parse() . '</p>';
	}

	/**
	 * @param array $data
	 * @return Status
	 */
	public function onSubmit( array $data ) {
		if ( !$this->title ) {
			return Status::newFatal( 'massmessage-edit-invalidtitle' );
		}

		$targets = self::parseInput( $data['content'] );
		if ( $targets === null ) {
			return Status::newFatal( 'massmessage-edit-invalidtargets' );
		}

		$result = MassMessageListContentHandler::edit(
			$this->title,
			$data['description'],
			$targets,
			'massmessage-edit-editsummary',
			$this->getContext()
		);

		if ( !$result->isGood() ) {
			return $result;
		}
		$this->getOutput()->redirect( $this->title->getFullUrl() );
	}

	public function onSuccess() {
		// No-op: We have already redirected.
	}

	/**
	 * Parse array of targets for editing.
	 * @var array $targets
	 * @return string
	 */
	protected static function parseTargets( $targets ) {
		global $wgCanonicalServer;

		$lines = array();
		foreach ( $targets as $target ) {
			if ( array_key_exists( 'site', $target ) ) {
				$lines[] = $target['title'] . '@' . $target['site'];
			} elseif ( strpos( $target['title'], '@' ) !== false ) {
				// List the site if it'd otherwise be ambiguous
				$lines[] = $target['title'] . '@' . MassMessage::getBaseUrl( $wgCanonicalServer );
			} else {
				$lines[] = $target['title'];
			}
		}
		return implode( "\n", $lines );
	}

	/**
	 * Parse user input into targets array. Returns null if input contains invalid data.
	 * @param string $input
	 * @return array|null
	 */
	protected static function parseInput( $input ) {
		$lines = array_filter( explode( "\n", $input ), 'trim' ); // Array of non-empty lines

		$targets = array();
		foreach ( $lines as $line ) {
			$target = MassMessageListContentHandler::extractTarget( $line );
			if ( array_key_exists( 'errors', $target ) ) {
				return null; // Invalid target
			}
			$targets[] = $target;
		}
		return MassMessageListContentHandler::normalizeTargetArray( $targets );
	}
}

<?php

class SpecialCreateMassMessageList extends FormSpecialPage {

	public function __construct() {
		parent::__construct( 'CreateMassMessageList' );
	}

	/**
	 * Add ResourceLoader module and call parent implementation.
	 * @param string $par
	 */
	public function execute( $par ) {
		$this->getOutput()->addModules( 'ext.MassMessage.create' );
		parent::execute( $par );
	}

	/**
	 * @return array
	 */
	protected function getFormFields() {
		return array(
			'title' => array(
				'type' => 'text',
				'label-message' => 'massmessage-create-title',
			),
			'description' => array(
				'type' => 'textarea',
				'rows' => 5,
				'label-message' => 'massmessage-create-description',
			),
			'content' => array(
				'type' => 'radio',
				'options' => $this->getContentOptions(),
				'default' => 'new',
				'label-message' => 'massmessage-create-content',
			),
			'source' => array(
				'type' => 'text',
				'label-message' => 'massmessage-create-source',
			),
		);
	}

	/**
	 * @param array $data
	 * @return Status
	 */
	public function onSubmit( array $data ) {
		$title = Title::newFromText( $data['title'] );
		if ( !$title ) {
			return Status::newFatal( 'massmessage-create-invalidtitle' );
		} elseif ( $title->exists() ) {
			return Status::newFatal( 'massmessage-create-exists' );
		} elseif ( !$title->userCan( 'edit' ) || !$title->userCan( 'create' ) ) {
			return Status::newFatal( 'massmessage-create-nopermission' );
		}

		if ( $data['content'] === 'import' ) { // Importing from an existing list
			$source = Title::newFromText( $data['source'] );
			if ( !$source ) {
				return Status::newFatal( 'massmessage-create-invalidsource' );
			}

			$targets = $this->getTargets( $source );
			if ( $targets === null ) {
				return Status::newFatal( 'massmessage-create-invalidsource' );
			}
		} else {
			$targets = array();
		}

		$result = MassMessageListContentHandler::edit(
			$title,
			$data['description'],
			$targets,
			$this->msg( 'massmessage-create-editsummary' )->inContentLanguage()->plain(),
			$this->getContext()
		);

		if ( !$result->isGood() ) {
			return $result;
		}

		$this->getOutput()->redirect( $title->getFullUrl() );
		return Status::newGood();
	}

	public function onSuccess() {
		// No-op: We have already redirected.
	}

	/**
	 * Build and return the aossociative array for the content radio button field.
	 * @return array
	 */
	protected function getContentOptions() {
		$mapping = array(
			'massmessage-create-new' => 'new',
			'massmessage-create-import' => 'import',
		);

		$options = array();
		foreach ( $mapping as $msgKey => $option ) {
			$options[$this->msg( $msgKey )->escaped()] = $option;
		}
		return $options;
	}

	/**
	 * Get targets from an existing delivery list or category; returns null on failure.
	 * @param Title $source
	 * @return array|null
	 */
	protected function getTargets( Title $source ) {
		$pages = MassMessageTargets::getTargets( $source, $this->getContext() );
		if ( $pages === null ) {
			return null;
		}

		$targets = array();
		foreach ( $pages as $page ) {
			$target = array( 'title' => $page['title'] );
			if ( $page['wiki'] !== wfWikiID() ) {
				$target['site'] = $page['site'];
			}
			$targets[] = $target;
		}
		return MassMessageListContentHandler::normalizeTargetArray( $targets );
	}
}

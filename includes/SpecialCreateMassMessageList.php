<?php

class SpecialCreateMassMessageList extends FormSpecialPage {

	public function __construct() {
		parent::__construct( 'CreateMassMessageList' );
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
				'disabled' => true,
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
		} else if ( $title->exists() ) {
			return Status::newFatal( 'massmessage-create-exists' );
		} else if ( !$title->userCan( 'edit' ) || !$title->userCan( 'create' ) ) {
			return Status::newFatal( 'massmessage-create-nopermission' );
		}

		if ( $data['content'] === 'import' ) {

			// TODO: Implement importing from existing lists
			$targets = array();

		} else {
			$targets = array();
		}

		$jsonText = FormatJson::encode(
			array( 'description' => $data['description'], 'targets' => $targets )
		);
		if ( !$jsonText ) {
			return Status::newFatal( 'massmessage-create-tojsonerror' );
		}

		$request = new DerivativeRequest(
			$this->getRequest(),
			array(
				'action' => 'edit',
				'title' => $title->getFullText(),
				'contentmodel' => 'MassMessageListContent',
				'text' => $jsonText,
				'summary' => $this->msg( 'massmessage-create-editsummary' )->plain(),
				'token' => $this->getUser()->getEditToken(),
			),
			true // Treat data as POSTed
		);

		try {
			$api = new ApiMain( $request, true );
			$api->execute();
		} catch ( UsageException $e ) {
			return Status::newFatal( $this->msg( 'massmessage-create-apierror',
				$e->getCodeString ) );
		}

		$this->getOutput()->redirect( $title->getFullUrl() );
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
}

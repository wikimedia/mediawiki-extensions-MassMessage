<?php

namespace MediaWiki\MassMessage\Specials;

use MediaWiki\HTMLForm\HTMLForm;
use MediaWiki\MassMessage\Content\MassMessageListContentHandler;
use MediaWiki\MassMessage\Lookup\SpamlistLookup;
use MediaWiki\MediaWikiServices;
use MediaWiki\SpecialPage\FormSpecialPage;
use MediaWiki\Status\Status;
use MediaWiki\Title\Title;
use MediaWiki\WikiMap\WikiMap;

class SpecialCreateMassMessageList extends FormSpecialPage {

	public function __construct() {
		parent::__construct( 'CreateMassMessageList', 'editcontentmodel' );
	}

	public function doesWrites() {
		return true;
	}

	/**
	 * Add ResourceLoader module and call parent implementation.
	 *
	 * @param string|null $par
	 */
	public function execute( $par ) {
		$this->addHelpLink( 'Help:Extension:MassMessage' );
		parent::execute( $par );
	}

	/**
	 * @return array
	 */
	protected function getFormFields() {
		$this->getOutput()->addModules( 'ext.MassMessage.create' );
		return [
			'title' => [
				'type' => 'text',
				'label-message' => 'massmessage-create-title',
			],
			'description' => [
				'type' => 'textarea',
				'rows' => 5,
				'useeditfont' => true,
				'label-message' => 'massmessage-create-description',
			],
			'content' => [
				'type' => 'radio',
				'options' => $this->getContentOptions(),
				'default' => 'new',
				'label-message' => 'massmessage-create-content',
			],
			'source' => [
				'type' => 'title',
				'creatable' => true,
				'label-message' => 'massmessage-create-source',
				'hide-if' => [ '!==', 'content', 'import' ],
			],
		];
	}

	/**
	 * Add an ID to the form for targeting with JS code.
	 *
	 * @param HTMLForm $form
	 */
	protected function alterForm( HTMLForm $form ) {
		$form->setId( 'mw-massmessage-create-form' );
	}

	/**
	 * @param array $data
	 * @return Status
	 */
	public function onSubmit( array $data ) {
		$title = Title::newFromText( $data['title'] );
		$pm = MediaWikiServices::getInstance()->getPermissionManager();
		if ( !$title ) {
			return Status::newFatal( 'massmessage-create-invalidtitle' );
		} elseif ( $title->exists() ) {
			return Status::newFatal( 'massmessage-create-exists' );
		} elseif ( !$pm->userCan( 'edit', $this->getUser(), $title ) ||
			!$pm->userCan( 'editcontentmodel', $this->getUser(), $title )
		) {
			return Status::newFatal( 'massmessage-create-nopermission' );
		}

		if ( $data['content'] === 'import' ) {
			// We're importing from an existing list
			$source = Title::newFromText( $data['source'] );
			if ( !$source ) {
				return Status::newFatal( 'massmessage-create-invalidsource' );
			}

			$targets = $this->getTargets( $source );
			if ( $targets === null || count( $targets ) === 0 ) {
				return Status::newFatal( 'massmessage-create-invalidsource' );
			}
			if ( $source->inNamespace( NS_CATEGORY ) ) {
				$editSummaryMsg = $this->msg(
					'massmessage-create-editsummary-catimport',
					$source->getPrefixedText()
				);
			} else {
				$editSummaryMsg = $this->msg(
					'massmessage-create-editsummary-import',
					$source->getPrefixedText(),
					$source->getLatestRevID()
				);
			}
		} else {
			$targets = [];
			$editSummaryMsg = $this->msg( 'massmessage-create-editsummary' );
		}

		$result = MassMessageListContentHandler::edit(
			$title,
			$data['description'],
			$targets,
			$editSummaryMsg->inContentLanguage()->plain(),
			false,
			'preferences',
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
	 * Build and return the associative array for the content radio button field.
	 *
	 * @return array
	 */
	protected function getContentOptions() {
		$mapping = [
			'massmessage-create-new' => 'new',
			'massmessage-create-import' => 'import',
		];

		$options = [];
		foreach ( $mapping as $msgKey => $option ) {
			$options[$this->msg( $msgKey )->escaped()] = $option;
		}
		return $options;
	}

	/**
	 * Get targets from an existing delivery list or category;
	 * returns null on failure.
	 *
	 * @param Title $source
	 * @return array|null
	 */
	protected function getTargets( Title $source ) {
		$pages = SpamlistLookup::getTargets(
			$source,
			/* $normalize = */ false
		);
		if ( $pages === null ) {
			return null;
		}

		$currentWikiId = WikiMap::getCurrentWikiId();
		$targets = [];
		foreach ( $pages as $page ) {
			$target = [ 'title' => $page['title'] ];
			if ( $page['wiki'] !== $currentWikiId ) {
				$target['site'] = $page['site'];
			}
			$targets[] = $target;
		}
		return MassMessageListContentHandler::normalizeTargetArray( $targets );
	}

	/**
	 * @return string
	 */
	protected function getDisplayFormat() {
		return 'ooui';
	}
}

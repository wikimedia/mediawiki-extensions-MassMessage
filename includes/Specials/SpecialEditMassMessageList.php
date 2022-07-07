<?php

namespace MediaWiki\MassMessage\Specials;

use EditPage;
use FormSpecialPage;
use Html;
use HTMLForm;
use LogEventsList;
use MediaWiki\MassMessage\Content\MassMessageListContent;
use MediaWiki\MassMessage\Content\MassMessageListContentHandler;
use MediaWiki\MassMessage\Lookup\DatabaseLookup;
use MediaWiki\MediaWikiServices;
use MediaWiki\Permissions\RestrictionStore;
use MediaWiki\Revision\RevisionRecord;
use MediaWiki\Revision\SlotRecord;
use MediaWiki\User\UserOptionsLookup;
use Status;
use Title;

class SpecialEditMassMessageList extends FormSpecialPage {

	/**
	 * The title of the list to edit
	 * If not null, the title refers to a delivery list.
	 *
	 * @var Title|null
	 */
	protected $title;

	/**
	 * The revision to edit
	 * If not null, the user can edit the delivery list.
	 *
	 * @var RevisionRecord|null
	 */
	protected $rev;

	/**
	 * The message key for the error encountered while parsing the title, if any
	 *
	 * @var string|null
	 */
	protected $errorMsgKey;

	/**
	 * Provides access to user options
	 *
	 * @var UserOptionsLookup
	 */
	private $userOptionsLookup;

	/** @var RestrictionStore */
	private $restrictionStore;

	/**
	 * @param UserOptionsLookup $userOptionsLookup
	 * @param RestrictionStore $restrictionStore
	 */
	public function __construct(
		UserOptionsLookup $userOptionsLookup,
		RestrictionStore $restrictionStore
	) {
		parent::__construct( 'EditMassMessageList' );

		$this->userOptionsLookup = $userOptionsLookup;
		$this->restrictionStore = $restrictionStore;
	}

	public function doesWrites() {
		return true;
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
			} else {
				$this->title = $title;
				$services = MediaWikiServices::getInstance();
				$revisionLookup = $services->getRevisionLookup();
				if ( !$services->getPermissionManager()->userCan( 'edit',
					$this->getUser(), $title )
				) {
					$this->errorMsgKey = 'massmessage-edit-nopermission';
				} else {
					$revId = $this->getRequest()->getInt( 'oldid' );
					if ( $revId > 0 ) {
						$rev = $revisionLookup->getRevisionById( $revId );
						if ( $rev
							&& $title->equals( $rev->getPageAsLinkTarget() )
							&& $rev->getSlot( SlotRecord::MAIN, RevisionRecord::RAW )
								->getModel() === 'MassMessageListContent'
							&& RevisionRecord::userCanBitfield(
								$rev->getVisibility(),
								RevisionRecord::DELETED_TEXT,
								$this->getUser()
							)
						) {
							$this->rev = $rev;
						} else { // Use the latest revision for the title if $rev is invalid.
							$this->rev = $revisionLookup->getRevisionByTitle( $title );
						}
					} else {
						$this->rev = $revisionLookup->getRevisionByTitle( $title );
					}
				}
			}
		}
	}

	/**
	 * Override the parent implementation to modify the page title and add a backlink.
	 */
	public function setHeaders() {
		parent::setHeaders();
		if ( $this->title ) {
			$out = $this->getOutput();

			// Page title
			$out->setPageTitle(
				$this->msg( 'massmessage-edit-pagetitle', $this->title->getPrefixedText() )
			);

			// Backlink
			if ( $this->rev ) {
				$revId = $this->rev->getId();
				$query = ( $revId !== $this->title->getLatestRevId() ) ?
					[ 'oldid' => $revId ] : [];
			} else {
				$query = [];
			}
			$out->addBacklinkSubtitle( $this->title, $query );

			// Edit notices; modified from EditPage::showHeader()
			if ( $this->rev ) {
				$out->addHTML(
					implode( "\n", $this->title->getEditNotices( $this->rev->getId() ) )
				);
			}

			// Protection warnings; modified from EditPage::showHeader()
			if ( $this->restrictionStore->isProtected( $this->title, 'edit' )
				&& MediaWikiServices::getInstance()->getPermissionManager()
					->getNamespaceRestrictionLevels( $this->title->getNamespace() ) !== [ '' ]
			) {
				if ( $this->restrictionStore->isSemiProtected( $this->title ) ) {
					$noticeMsg = 'semiprotectedpagewarning';
				} else { // Full protection
					$noticeMsg = 'protectedpagewarning';
				}
				LogEventsList::showLogExtract( $out, 'protect', $this->title, '',
					[ 'lim' => 1, 'msgKey' => [ $noticeMsg ] ] );
			}
		}
	}

	/**
	 * @return array
	 */
	protected function getFormFields() {
		// Return an empty form if the title is invalid or if the user can't edit the list.
		if ( !$this->rev ) {
			return [];
		}

		$this->getOutput()->addModules( 'ext.MassMessage.edit' );

		/**
		 * @var MassMessageListContent $content
		 */
		$content = $this->rev->getContent(
			SlotRecord::MAIN,
			RevisionRecord::FOR_THIS_USER,
			$this->getUser()
		);
		$description = $content->getDescription();
		$targets = $content->getTargetStrings();

		return [
			'description' => [
				'type' => 'textarea',
				'rows' => 5,
				'default' => $description ?? '',
				'useeditfont' => true,
				'label-message' => 'massmessage-edit-description',
			],
			'content' => [
				'type' => 'textarea',
				'default' => ( $targets !== null ) ? implode( "\n", $targets ) : '',
				'label-message' => 'massmessage-edit-content',
			],
			'summary' => [
				'type' => 'text',
				'maxlength' => \CommentStore::COMMENT_CHARACTER_LIMIT,
				'size' => 60,
				'label-message' => 'massmessage-edit-summary',
			],
		];
	}

	/**
	 * Hide the form if the title is invalid or if the user can't edit the list.
	 *
	 * @param HTMLForm $form
	 */
	protected function alterForm( HTMLForm $form ) {
		if ( !$this->rev ) {
			$form->setWrapperLegend( false );
			$form->suppressDefaultSubmit( true );
		}
	}

	/**
	 * Return instructions for the form and / or warnings.
	 *
	 * @return string
	 */
	protected function preHtml() {
		$allowGlobalMessaging = $this->getConfig()->get( 'AllowGlobalMessaging' );

		if ( $this->rev ) {
			// Instructions
			if ( $allowGlobalMessaging && count( DatabaseLookup::getDatabases() ) > 1 ) {
				$headerKey = 'massmessage-edit-headermulti';
			} else {
				$headerKey = 'massmessage-edit-header';
			}
			$html = Html::rawElement( 'p', [], $this->msg( $headerKey )->parse() );

			// Deleted revision warning
			if ( $this->rev->isDeleted( RevisionRecord::DELETED_TEXT ) ) {
				$html .= Html::openElement( 'div', [ 'class' => 'mw-warning plainlinks' ] );
				$html .= Html::rawElement( 'p', [],
					$this->msg( 'rev-deleted-text-view' )->parse() );
				$html .= Html::closeElement( 'div' );
			}

			// Old revision warning
			if ( $this->rev->getId() !== $this->title->getLatestRevID() ) {
				$html .= Html::rawElement( 'p', [], $this->msg( 'editingold' )->parse() );
			}
		} else {
			// Error determined in setParameter()
			$html = Html::rawElement( 'p', [], $this->msg( $this->errorMsgKey )->parse() );
		}
		return $html;
	}

	/**
	 * Return a copyright warning to be displayed below the form.
	 *
	 * @return string
	 */
	protected function postHtml() {
		if ( $this->rev ) {
			return EditPage::getCopyrightWarning( $this->title, 'parse', $this );
		} else {
			return '';
		}
	}

	/**
	 * @param array $data
	 * @param HTMLForm|null $form
	 * @return Status
	 */
	public function onSubmit( array $data, HTMLForm $form = null ) {
		if ( !$this->title ) {
			return Status::newFatal( 'massmessage-edit-invalidtitle' );
		}

		// Parse input into target array.
		$parseResult = self::parseInput( $data['content'] );
		if ( !$parseResult->isGood() ) {
			// Wikitext list of escaped invalid target strings
			$invalidList = '* ' . implode( "\n* ", array_map( 'wfEscapeWikiText',
				$parseResult->value ) );
			return Status::newFatal( $this->msg( 'massmessage-edit-invalidtargets',
				count( $parseResult->value ), $invalidList ) );
		}

		// Blank edit summary warning
		if ( $data['summary'] === ''
			&& $this->userOptionsLookup->getOption( $this->getUser(), 'forceeditsummary' )
			&& !$this->getRequest()->getCheck( 'summarywarned' )
		) {
			$form->addHiddenField( 'summarywarned', 'true' );
			return Status::newFatal( $this->msg( 'massmessage-edit-missingsummary' ) );
		}

		$editResult = MassMessageListContentHandler::edit(
			$this->title,
			$data['description'],
			$parseResult->value,
			$data['summary'],
			$this->getContext()
		);

		if ( !$editResult->isGood() ) {
			return $editResult;
		}

		$this->getOutput()->redirect( $this->title->getFullURL() );
		return Status::newGood();
	}

	public function onSuccess() {
		// No-op: We have already redirected.
	}

	/**
	 * Parse user input into an array of targets and return it as the value of a Status object.
	 * If input contains invalid data, the value is the array of invalid target strings.
	 *
	 * @param string $input
	 * @return Status
	 */
	protected static function parseInput( $input ) {
		$lines = array_filter( explode( "\n", $input ), 'trim' ); // Array of non-empty lines

		$targets = [];
		$invalidTargets = [];
		foreach ( $lines as $line ) {
			$target = MassMessageListContentHandler::extractTarget( $line );
			if ( array_key_exists( 'errors', $target ) ) {
				$invalidTargets[] = $line;
			}
			$targets[] = $target;
		}

		$result = new Status;
		if ( empty( $invalidTargets ) ) {
			$result->setResult( true,
				MassMessageListContentHandler::normalizeTargetArray( $targets ) );
		} else {
			$result->setResult( false, $invalidTargets );
		}
		return $result;
	}

	/**
	 * @return bool
	 */
	public function isListed() {
		return false;
	}

	/**
	 * @return string
	 */
	protected function getDisplayFormat() {
		return 'ooui';
	}
}

<?php

namespace MediaWiki\MassMessage\Specials;

use LogEventsList;
use MediaWiki\CommentStore\CommentStore;
use MediaWiki\EditPage\EditPage;
use MediaWiki\Html\Html;
use MediaWiki\HTMLForm\HTMLForm;
use MediaWiki\MassMessage\Content\MassMessageListContent;
use MediaWiki\MassMessage\Content\MassMessageListContentHandler;
use MediaWiki\MassMessage\Lookup\DatabaseLookup;
use MediaWiki\Permissions\PermissionManager;
use MediaWiki\Permissions\RestrictionStore;
use MediaWiki\Revision\RevisionLookup;
use MediaWiki\Revision\RevisionRecord;
use MediaWiki\Revision\SlotRecord;
use MediaWiki\SpecialPage\FormSpecialPage;
use MediaWiki\Status\Status;
use MediaWiki\Title\Title;
use MediaWiki\User\Options\UserOptionsLookup;
use MediaWiki\Watchlist\WatchlistManager;
use Wikimedia\Rdbms\IDBAccessObject;

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

	/** @var WatchlistManager */
	private $watchlistManager;

	/** @var PermissionManager */
	private $permissionManager;

	/** @var RevisionLookup */
	private $revisionLookup;

	/**
	 * @param UserOptionsLookup $userOptionsLookup
	 * @param RestrictionStore $restrictionStore
	 * @param WatchlistManager $watchlistManager
	 * @param PermissionManager $permissionManager
	 * @param RevisionLookup $revisionLookup
	 */
	public function __construct(
		UserOptionsLookup $userOptionsLookup,
		RestrictionStore $restrictionStore,
		WatchlistManager $watchlistManager,
		PermissionManager $permissionManager,
		RevisionLookup $revisionLookup
	) {
		parent::__construct( 'EditMassMessageList' );

		$this->userOptionsLookup = $userOptionsLookup;
		$this->restrictionStore = $restrictionStore;
		$this->watchlistManager = $watchlistManager;
		$this->permissionManager = $permissionManager;
		$this->revisionLookup = $revisionLookup;
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
				if ( !$this->permissionManager->userCan( 'edit',
					$this->getUser(), $title )
				) {
					$this->errorMsgKey = 'massmessage-edit-nopermission';
				} else {
					$revId = $this->getRequest()->getInt( 'oldid' );
					if ( $revId > 0 ) {
						$rev = $this->revisionLookup->getRevisionById( $revId );
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
						} else {
							// Use the latest revision for the title if $rev is invalid.
							$this->rev = $this->revisionLookup->getRevisionByTitle( $title );
						}
					} else {
						$this->rev = $this->revisionLookup->getRevisionByTitle( $title );
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
			$out->setPageTitleMsg(
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
				&& $this->permissionManager
					->getNamespaceRestrictionLevels( $this->title->getNamespace() ) !== [ '' ]
			) {
				if ( $this->restrictionStore->isSemiProtected( $this->title ) ) {
					$noticeMsg = 'semiprotectedpagewarning';
				} else {
					// Full protection
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

		$this->getOutput()->addModules( [ 'ext.MassMessage.edit', 'ext.MassMessage.styles' ] );

		/**
		 * @var MassMessageListContent $content
		 */
		$content = $this->rev->getContent(
			SlotRecord::MAIN,
			RevisionRecord::FOR_THIS_USER,
			$this->getUser()
		);
		'@phan-var MassMessageListContent $content';
		$description = $content->getDescription();
		$targets = $content->getTargetStrings();

		$fields = [
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
				'maxlength' => CommentStore::COMMENT_CHARACTER_LIMIT,
				'size' => 60,
				'label-message' => 'summary',
			],
		];

		if ( $this->permissionManager->userHasRight( $this->getUser(), 'minoredit' ) ) {
			$fields['minor'] = [
				'name' => 'minor',
				'id' => 'wpMinoredit',
				'type' => 'check',
				'label-message' => 'minoredit',
				'default' => false,
			];
		}

		if ( $this->getUser()->isNamed() ) {
			$fields['watch'] = [
				'name' => 'watch',
				'id' => 'wpWatchthis',
				'type' => 'check',
				'label-message' => 'watchthis',
				'default' => $this->watchlistManager->isWatched( $this->getUser(), $this->title ) ||
					$this->userOptionsLookup->getOption( $this->getUser(), 'watchdefault' ),
			];
		}

		return $fields + [
			'copyright' => [
				'type' => 'info',
				'default' => EditPage::getCopyrightWarning( $this->title, 'parse', $this ),
				'raw' => true,
			],
		];
	}

	/**
	 * Hide the form if the title is invalid or if the user can't edit the list. If neither
	 * of these are true, then add a cancel button alongside the automatic save button. Also
	 * add an ID to the form for targeting with CSS styles.
	 *
	 * @param HTMLForm $form
	 */
	protected function alterForm( HTMLForm $form ) {
		if ( !$this->rev ) {
			$form->setWrapperLegend( false );
			$form->suppressDefaultSubmit();
		} else {
			$form->showCancel();
			$form->setCancelTarget( $this->title );
		}
		$form->setId( 'mw-massmessage-edit-form' );
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
			$html = $this->msg( $headerKey )->parseAsBlock();

			// Deleted revision warning
			if ( $this->rev->isDeleted( RevisionRecord::DELETED_TEXT ) ) {
				$html .= Html::openElement( 'div', [ 'class' => 'mw-warning plainlinks' ] );
				$html .= $this->msg( 'rev-deleted-text-view' )->parseAsBlock();
				$html .= Html::closeElement( 'div' );
			}

			// Old revision warning
			if ( $this->rev->getId() !== $this->title->getLatestRevID( IDBAccessObject::READ_LATEST ) ) {
				$html .= $this->msg( 'editingold' )->parseAsBlock();
			}
		} else {
			// Error determined in setParameter()
			$html = $this->msg( $this->errorMsgKey )->parseAsBlock();
		}
		return $html;
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
			$this->permissionManager->userHasRight( $this->getUser(), 'minoredit' ) && $data['minor'],
			$data['watch'] ? 'watch' : 'unwatch',
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
		// Array of non-empty lines
		$lines = array_filter( explode( "\n", $input ), 'trim' );

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
		if ( !$invalidTargets ) {
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

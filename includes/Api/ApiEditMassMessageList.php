<?php

namespace MediaWiki\MassMessage\Api;

use MediaWiki\Api\ApiBase;
use MediaWiki\Api\ApiMain;
use MediaWiki\Api\ApiWatchlistTrait;
use MediaWiki\MassMessage\Content\MassMessageListContent;
use MediaWiki\MassMessage\Content\MassMessageListContentHandler;
use MediaWiki\MediaWikiServices;
use MediaWiki\Revision\SlotRecord;
use MediaWiki\Title\Title;
use Wikimedia\ParamValidator\ParamValidator;

/**
 * API module to edit a MassMessage delivery list.
 *
 * @ingroup API
 */

class ApiEditMassMessageList extends ApiBase {

	use ApiWatchlistTrait;

	/** @inheritDoc */
	public function __construct( ApiMain $mainModule, $moduleName, $modulePrefix = '' ) {
		parent::__construct( $mainModule, $moduleName, $modulePrefix );

		// Needed for ApiWatchlistTrait.
		$this->watchlistExpiryEnabled = false;
	}

	public function execute() {
		$data = $this->extractRequestParams();

		$this->requireAtLeastOneParameter( $data, 'add', 'remove', 'description' );

		$spamlist = Title::newFromText( $data['spamlist'] );
		if ( $spamlist === null
			|| !$spamlist->exists()
			|| !$spamlist->hasContentModel( 'MassMessageListContent' )
		) {
			$this->dieWithError( 'apierror-massmessage-invalidspamlist', 'invalidspamlist' );
		}
		'@phan-var Title $spamlist';

		/**
		 * @var MassMessageListContent $content
		 */
		$content = MediaWikiServices::getInstance()
			->getRevisionLookup()
			->getRevisionByTitle( $spamlist )
			->getContent( SlotRecord::MAIN );
		'@phan-var MassMessageListContent $content';
		$description = $content->getDescription();
		$targets = $content->getTargets();

		// Create a copy.
		$newTargets = $targets;

		if ( isset( $data['add'] ) ) {
			$invalidAdd = [];

			foreach ( $data['add'] as $page ) {
				$target = MassMessageListContentHandler::extractTarget( $page );
				if ( isset( $target['errors'] ) ) {
					$item = [ '*' => $page ];
					foreach ( $target['errors'] as $error ) {
						$item[$error] = '';
					}
					$invalidAdd[] = $item;
				} else {
					$newTargets[] = $target;
				}
			}

			// Remove duplicates
			$newTargets = MassMessageListContentHandler::normalizeTargetArray( $newTargets );
			$invalidAdd = array_unique( $invalidAdd, SORT_REGULAR );
		}

		if ( isset( $data['remove'] ) ) {
			$toRemove = [];
			$invalidRemove = [];

			foreach ( $data['remove'] as $page ) {
				$target = MassMessageListContentHandler::extractTarget( $page );
				if ( isset( $target['errors'] ) || !in_array( $target, $newTargets ) ) {
					$invalidRemove[] = $page;
				} else {
					$toRemove[] = $target;
				}
			}

			// In case there are duplicates within the provided list
			$toRemove = MassMessageListContentHandler::normalizeTargetArray( $toRemove );
			$invalidRemove = array_unique( $invalidRemove );

			$newTargets = array_values( array_udiff( $newTargets, $toRemove,
				[ MassMessageListContentHandler::class, 'compareTargets' ] ) );
		}

		if ( isset( $data['add'] ) ) {
			$added = array_values( array_udiff( $newTargets, $targets,
				[ MassMessageListContentHandler::class, 'compareTargets' ] ) );
		} else {
			$added = [];
		}

		if ( isset( $data['remove'] ) ) {
			$removed = array_values( array_udiff( $targets, $newTargets,
				[ MassMessageListContentHandler::class, 'compareTargets' ] ) );
		} else {
			$removed = [];
		}

		$description = $oldDescription = $content->getDescription();
		$descriptionChanged = false;
		if ( isset( $data['description'] ) ) {
			$description = $data['description'];
			$descriptionChanged = ( $description !== $oldDescription );
		}

		// Make an edit only if there are added or removed pages, or the description changed
		if ( $added || $removed || $descriptionChanged ) {
			$summary = $this->getEditSummary( $added, $removed, $descriptionChanged );
			$editResult = MassMessageListContentHandler::edit(
				$spamlist,
				$description,
				$newTargets,
				$summary,
				$this->getPermissionManager()->userHasRight( $this->getUser(), 'minoredit' ) &&
					$data['minor'],
				$data['watchlist'],
				// We can pass $this because APIs implement IContextSource
				$this
			);
			if ( !$editResult->isGood() ) {
				$this->dieStatus( $editResult );
			}
		}

		$result = $this->getResult();
		$resultArray = [ 'result' => 'Success' ];

		if ( isset( $data['add'] ) ) {
			$resultArray['added'] = $added;

			// Use a LinkBatch to look up and cache existence for all local targets
			$lb = MediaWikiServices::getInstance()->getLinkBatchFactory()->newLinkBatch();
			foreach ( $resultArray['added'] as $target ) {
				if ( !isset( $target['site'] ) ) {
					$lb->addObj( Title::newFromText( $target['title'] ) );
				}
			}
			$lb->execute();

			// Add an empty "missing" attribute to new local targets that do not exist
			foreach ( $resultArray['added'] as &$target ) {
				if ( !isset( $target['site'] )
					&& !Title::newFromText( $target['title'] )->exists()
				) {
					$target['missing'] = '';
				}
			}

			$result->setIndexedTagName( $resultArray['added'], 'page' );

			if ( !empty( $invalidAdd ) ) {
				$resultArray['result'] = 'Done';
				$resultArray['invalidadd'] = $invalidAdd;
				$result->setIndexedTagName( $resultArray['invalidadd'], 'item' );
			}
		}

		if ( isset( $data['remove'] ) ) {
			$resultArray['removed'] = $removed;
			$result->setIndexedTagName( $resultArray['removed'], 'page' );

			if ( !empty( $invalidRemove ) ) {
				$resultArray['result'] = 'Done';
				$resultArray['invalidremove'] = $invalidRemove;
				$result->setIndexedTagName( $resultArray['invalidremove'], 'item' );
			}
		}

		if ( isset( $data['description'] ) ) {
			if ( $descriptionChanged ) {
				$resultArray['description'] = $description;
			} else {
				$resultArray['result'] = 'Done';
				$resultArray['invaliddescription'] = $description;
			}
		}

		$result->addValue(
			null,
			$this->getModuleName(),
			$resultArray
		);
	}

	/**
	 * Get the edit summary.
	 * @todo add the actual new description to the summary, rather than noting that it changed
	 *
	 * @param array $added
	 * @param array $removed
	 * @param bool $descriptionChanged
	 * @return string
	 */
	protected function getEditSummary( $added, $removed, $descriptionChanged ) {
		$msgChange = ( $descriptionChanged ? 'change' : '' );
		if ( $added && $removed ) {
			// * massmessage-summary-addremove
			// * massmessage-summary-addremovechange
			$summaryMsg = $this->msg( 'massmessage-summary-addremove' . $msgChange )
				->numParams( count( $added ) )
				->numParams( count( $removed ) );
		} elseif ( $added && !$removed ) {
			if ( count( $added ) === 1 ) {
				if ( isset( $added[0]['site'] ) ) {
					// * massmessage-summary-addonsite
					// * massmessage-summary-addonsitechange
					$summaryMsg = $this->msg(
						'massmessage-summary-addonsite' . $msgChange,
						$added[0]['title'],
						$added[0]['site']
					);
				} else {
					// * massmessage-summary-add
					// * massmessage-summary-addchange
					$summaryMsg = $this->msg(
						'massmessage-summary-add' . $msgChange,
						$added[0]['title']
					);
				}
			} else {
				// * massmessage-summary-addmulti
				// * massmessage-summary-addmultichange
				$summaryMsg = $this->msg( 'massmessage-summary-addmulti' . $msgChange )
					->numParams( count( $added ) );
			}
		} elseif ( !$added && $removed ) {
			if ( count( $removed ) === 1 ) {
				if ( isset( $removed[0]['site'] ) ) {
					// * massmessage-summary-removeonsite
					// * massmessage-summary-removeonsitechange
					$summaryMsg = $this->msg(
						'massmessage-summary-removeonsite' . $msgChange,
						$removed[0]['title'],
						$removed[0]['site']
					);
				} else {
					// * massmessage-summary-remove
					// * massmessage-summary-removechange
					$summaryMsg = $this->msg(
						'massmessage-summary-remove' . $msgChange,
						$removed[0]['title']
					);
				}
			} else {
				// * massmessage-summary-removemulti
				// * massmessage-summary-removemultichange
				$summaryMsg = $this->msg( 'massmessage-summary-removemulti' . $msgChange )
					->numParams( count( $removed ) );
			}
		} else {
			$summaryMsg = $this->msg( 'massmessage-summary-change' );
		}
		return $summaryMsg->inContentLanguage()->text();
	}

	/** @inheritDoc */
	public function getAllowedParams() {
		return [
			'spamlist' => [
				ParamValidator::PARAM_TYPE => 'string',
				ParamValidator::PARAM_REQUIRED => true
			],
			'description' => [
				ParamValidator::PARAM_TYPE => 'string'
			],
			'add' => [
				ParamValidator::PARAM_TYPE => 'string',
				ParamValidator::PARAM_ISMULTI => true
			],
			'remove' => [
				ParamValidator::PARAM_TYPE => 'string',
				ParamValidator::PARAM_ISMULTI => true
			],
			'minor' => [
				ParamValidator::PARAM_TYPE => 'boolean',
				ParamValidator::PARAM_DEFAULT => false
			],
		] + $this->getWatchlistParams() + [ 'token' => null ];
	}

	/** @inheritDoc */
	public function mustBePosted() {
		return true;
	}

	/** @inheritDoc */
	public function needsToken() {
		return 'csrf';
	}

	/** @inheritDoc */
	public function isWriteMode() {
		return true;
	}

	/** @inheritDoc */
	protected function getExamplesMessages() {
		return [
			'action=editmassmessagelist&spamlist=Example&add=User%20talk%3AFoo%7CTalk%3ABar' .
			'&remove=Talk%3ABaz&token=TOKEN'
				=> 'apihelp-editmassmessagelist-example-1',
			'action=editmassmessagelist&spamlist=Example' .
			'&description=FooBor%20delivery%20services&token=TOKEN'
				=> 'apihelp-editmassmessagelist-example-2'
		];
	}

	/** @inheritDoc */
	public function getHelpUrls() {
		return [ 'https://www.mediawiki.org/wiki/Extension:MassMessage/API' ];
	}

}

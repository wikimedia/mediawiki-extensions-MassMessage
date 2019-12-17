<?php

namespace MediaWiki\MassMessage;

use ApiBase;
use LinkBatch;
use Revision;
use Title;

/**
 * API module to edit a MassMessage delivery list.
 *
 * @ingroup API
 */

class ApiEditMassMessageList extends ApiBase {

	public function execute() {
		$data = $this->extractRequestParams();

		// Must add or remove pages (or both) for a meaningful request
		$this->requireAtLeastOneParameter( $data, 'add', 'remove' );

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
		$content = Revision::newFromTitle( $spamlist )->getContent();
		$description = $content->getDescription();
		$targets = $content->getTargets();
		$newTargets = $targets; // Create a copy.

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

		// Make an edit only if there are added or removed pages
		if ( !empty( $added ) || !empty( $removed ) ) {
			$summary = $this->getEditSummary( $added, $removed );
			$editResult = MassMessageListContentHandler::edit(
				$spamlist,
				$description,
				$newTargets,
				$summary,
				$this // APIs implement IContextSource
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
			$lb = new LinkBatch;
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

		$result->addValue(
			null,
			$this->getModuleName(),
			$resultArray
		);
	}

	/**
	 * Get the edit summary.
	 *
	 * @param array $added
	 * @param array $removed
	 * @return string
	 */
	protected function getEditSummary( $added, $removed ) {
		if ( !empty( $added ) && !empty( $removed ) ) {
			$summaryMsg = $this->msg( 'massmessage-summary-addremove' )
				->numParams( count( $added ) )
				->numParams( count( $removed ) );
		} elseif ( !empty( $added ) ) { // Only added
			if ( count( $added ) === 1 ) {
				if ( isset( $added[0]['site'] ) ) {
					$summaryMsg = $this->msg(
						'massmessage-summary-addonsite',
						$added[0]['title'],
						$added[0]['site']
					);
				} else {
					$summaryMsg = $this->msg(
						'massmessage-summary-add',
						$added[0]['title']
					);
				}
			} else {
				$summaryMsg = $this->msg( 'massmessage-summary-addmulti' )
					->numParams( count( $added ) );
			}
		} else { // Only removed
			if ( count( $removed ) === 1 ) {
				if ( isset( $removed[0]['site'] ) ) {
					$summaryMsg = $this->msg(
						'massmessage-summary-removeonsite',
						$removed[0]['title'],
						$removed[0]['site']
					);
				} else {
					$summaryMsg = $this->msg(
						'massmessage-summary-remove',
						$removed[0]['title']
					);
				}
			} else {
				$summaryMsg = $this->msg( 'massmessage-summary-removemulti' )
					->numParams( count( $removed ) );
			}
		}
		return $summaryMsg->inContentLanguage()->text();
	}

	public function getAllowedParams() {
		return [
			'spamlist' => [
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_REQUIRED => true
			],
			'add' => [
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_ISMULTI => true
			],
			'remove' => [
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_ISMULTI => true
			],
			'token' => null,
		];
	}

	public function mustBePosted() {
		return true;
	}

	public function needsToken() {
		return 'csrf';
	}

	public function isWriteMode() {
		return true;
	}

	/**
	 * @see ApiBase::getExamplesMessages()
	 *
	 * @return array
	 */
	protected function getExamplesMessages() {
		return [
			'action=editmassmessagelist&spamlist=Example&add=User%20talk%3AFoo%7CTalk%3ABar' .
			'&remove=Talk%3ABaz&token=TOKEN'
				=> 'apihelp-editmassmessagelist-example-1',
		];
	}

	public function getHelpUrls() {
		return [ 'https://www.mediawiki.org/wiki/Extension:MassMessage/API' ];
	}

}

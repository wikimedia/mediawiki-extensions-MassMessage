<?php
/**
 * API module to edit a MassMessage delivery list
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
			$this->dieUsage( 'The specified spamlist is invalid', 'invalidspamlist' );
		}

		/** @var MassMessageListContent $content */
		$content = Revision::newFromTitle( $spamlist )->getContent();
		$description = $content->getDescription();
		$targets = $content->getTargets();
		$newTargets = $targets; // Create a copy.

		if ( isset( $data['add'] ) ) {
			$invalidAdd = array();

			foreach ( $data['add'] as $page ) {
				$target = MassMessageListContentHandler::extractTarget( $page );
				if ( isset( $target['errors'] ) ) {
					$item = array( '*' => $page );
					foreach( $target['errors'] as $error ) {
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
			$toRemove = array();
			$invalidRemove = array();

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
				'MassMessageListContentHandler::compareTargets' ) );
		}

		if ( isset( $data['add'] ) ) {
			$added = array_values( array_udiff( $newTargets, $targets,
				'MassMessageListContentHandler::compareTargets' ) );
		} else {
			$added = array();
		}

		if ( isset( $data['remove'] ) ) {
			$removed = array_values( array_udiff( $targets, $newTargets,
				'MassMessageListContentHandler::compareTargets' ) );
		} else {
			$removed = array();
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
		$resultArray = array( 'result' => 'Success' );

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
	 * Get the edit summary
	 * @param array $added
	 * @param array $removed
	 * @return string
	 */
	protected function getEditSummary( $added, $removed ) {
		if ( !empty( $added ) && !empty( $removed ) ) {
			return $this->msg( 'massmessage-summary-addremove' )
				->numParams( count( $added ) )
				->numParams( count( $removed ) )
				->inContentLanguage()->text();
		}

		if ( !empty( $added ) ) { // Only added
			if ( count( $added ) === 1 ) {
				if ( isset( $added[0]['site'] ) ) {
					$key = 'massmessage-summary-addonsite';
					$title = $added[0]['title'];
					$site = $added[0]['site'];
				} else {
					$key = 'massmessage-summary-add';
					$title = $added[0]['title'];
				}
			} else {
				$key = 'massmessage-summary-addmulti';
				$count = count( $added );
			}
		} else { // Only removed
			if ( count( $removed ) === 1 ) {
				if ( isset( $removed[0]['site'] ) ) {
					$key = 'massmessage-summary-removeonsite';
					$title = $removed[0]['title'];
					$site = $removed[0]['site'];
				} else {
					$key = 'massmessage-summary-remove';
					$title = $removed[0]['title'];
				}
			} else {
				$key = 'massmessage-summary-removemulti';
				$count = count( $removed );
			}
		}

		if ( isset( $site ) ) { // Added or removed page on another wiki
			return $this->msg( $key, $title, $site )->inContentLanguage()->plain();
		} elseif ( isset( $title ) ) { // Added or removed a page on the local wiki
			return $this->msg( $key, $title )->inContentLanguage()->plain();
		} else { // Added or removed multiple pages
			return $this->msg( $key )->numParams( $count )->inContentLanguage()->text();
		}
	}

	public function getDescription() {
		return 'Edit a mass message delivery list';
	}

	public function getAllowedParams() {
		return array(
			'spamlist' => array(
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_REQUIRED => true
			),
			'add' => array(
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_ISMULTI => true
			),
			'remove' => array(
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_ISMULTI => true
			),
			'token' => null,
		);
	}

	public function getParamDescription() {
		return array(
			'spamlist' => 'Title of the delivery list to update',
			'add' => 'Titles to add to the list',
			'remove' => 'Titles to remove from the list',
			'token' => 'An edit token from action=tokens'
		);
	}

	public function mustBePosted() {
		return true;
	}


	public function needsToken() {
		return 'csrf';
	}

	public function getTokenSalt() {
		return '';
	}

	public function isWriteMode() {
		return true;
	}

	public function getExamples() {
		return array(
			'api.php?action=editmassmessagelist&spamlist=Example&add=User%20talk%3AFoo%7CTalk%3ABar&remove=Talk%3ABaz&token=TOKEN'
			=> 'Add [[User talk:Foo]] and [[Talk:Bar]] to the delivery list [[Example]] and remove [[Talk:Baz]] from it'
		);
	}

	public function getHelpUrls() {
		return array( 'https://www.mediawiki.org/wiki/Extension:MassMessage/API' );
	}

}

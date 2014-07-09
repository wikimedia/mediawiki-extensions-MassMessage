<?php

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

		$content = Revision::newFromTitle( $spamlist )->getContent();
		$description = $content->getDescription();
		$targets = $content->getTargets();
		$newTargets = $targets; // Create a copy.

		if ( isset( $data['add'] ) ) {
			foreach ( $data['add'] as $page ) {
				$target = MassMessageListContentHandler::extractTarget( $page );
				if ( $target === null ) {
					$this->dieUsage( 'One of the pages to be added is invalid', 'invalidadd' );
				}
				$newTargets[] = $target;
			}
			// Remove duplicates
			$newTargets = MassMessageListContentHandler::normalizeTargetArray( $newTargets );
		}

		if ( isset( $data['remove'] ) ) {
			$toRemove = array();
			foreach ( $data['remove'] as $page ) {
				$target = MassMessageListContentHandler::extractTarget( $page );
				if ( $target === null || !in_array( $target, $newTargets ) ) {
					$this->dieUsage(
						'One of the pages to be removed is invalid or not in the spamlist',
						'invalidremove'
					);
				}
				$toRemove[] = $target;
			}
			// In case there are duplicates within the provided list
			$toRemove = MassMessageListContentHandler::normalizeTargetArray( $toRemove );

			$newTargets = array_values( array_udiff( $newTargets, $toRemove,
				'MassMessageListContentHandler::compareTargets' ) );
		}

		$result = MassMessageListContentHandler::edit(
			$spamlist,
			$description,
			$newTargets,
			'massmessage-api-editsummary',
			$this->getContext()
		);
		if ( !$result->isGood() ) {
			$this->dieStatus( $result );
		}

		$resultArray = array( 'result' => 'Success' );
		if ( isset( $data['add'] ) ) {
			$resultArray['added'] = count( array_udiff( $newTargets, $targets,
				'MassMessageListContentHandler::compareTargets' ) );
		}
		if ( isset( $data['remove'] ) ) {
			$resultArray['removed'] = count( array_udiff( $targets, $newTargets,
				'MassMessageListContentHandler::compareTargets' ) );
		}
		$this->getResult()->addValue(
			null,
			$this->getModuleName(),
			$resultArray
		);
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

	public function getPossibleErrors() {
		return array_merge(
			parent::getPossibleErrors(),
			array(
				array( 'invalidspamlist' ),
				array( 'invalidadd' ),
				array( 'invalidremove' ),
				array( 'massmessage-content-tojsonerror' ),
				array( 'massmessage-content-apierror' ),
			)
		);
	}

	public function getResultProperties() {
		return array(
			'' => array(
				'result' => array(
					ApiBase::PROP_TYPE => 'string'
				),
				'added' => array(
					ApiBase::PROP_TYPE => 'integer',
					ApiBase::PROP_NULLABLE => true
				),
				'removed' => array(
					ApiBase::PROP_TYPE => 'integer',
					ApiBase::PROP_NULLABLE => true
				),
			)
		);
	}

	public function mustBePosted() {
		return true;
	}


	public function needsToken() {
		return true;
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

<?php
declare( strict_types = 1 );

namespace MediaWiki\MassMessage\Api;

use ApiQueryBase;
use MediaWiki\MassMessage\Content\MassMessageListContent;
use MediaWiki\MediaWikiServices;
use Title;

/**
 * API module to retrieve the content of a mass message distribution list
 *
 * @ingroup API
 */

class ApiQueryMMContent extends ApiQueryBase {

	public function execute() {
		$pageSet = $this->getPageSet();
		$pageids = array_keys( $pageSet->getGoodTitles() );
		if ( !$pageids ) {
			return true;
		}

		$spamlists = [];
		foreach ( $pageids as $pageid ) {
			$spamlist = Title::newFromId( $pageid );
			if ( $spamlist === null
				|| !$spamlist->exists()
				|| !$spamlist->hasContentModel( 'MassMessageListContent' )
			) {
				$this->dieWithError( 'apierror-massmessage-invalidspamlist', 'invalidspamlist' );
			}
			$spamlists[ $pageid ] = $spamlist;
		}

		$result = $this->getResult();
		$wikiPageFactory = MediaWikiServices::getInstance()->getWikiPageFactory();

		foreach ( $spamlists as $pageid => $spamlist ) {
			$content = $wikiPageFactory->newFromTitle( $spamlist )->getContent();
			if ( !$content instanceof MassMessageListContent ) {
				$this->dieWithError( 'apierror-massmessage-invalidspamlist', 'invalidspamlist' );
			}

			$result->addValue(
				[ 'query', 'pages', $pageid, 'mmcontent' ],
				'description',
				$content->getDescription()
			);
			$result->addValue(
				[ 'query', 'pages', $pageid, 'mmcontent' ],
				'targets',
				$content->getTargetStrings()
			);
		}
		return true;
	}

	/**
	 * @param array $params
	 * @return string
	 */
	public function getCacheMode( $params ) {
		return 'public';
	}

	public function getAllowedParams() {
		return [];
	}

	/**
	 * @see ApiBase::getExamplesMessages()
	 * @return array
	 */
	protected function getExamplesMessages() {
		return [
			'action=query&prop=info|mmcontent&titles=Spam%20list'
				=> 'apihelp-query+mmcontent-example-1',
		];
	}
}

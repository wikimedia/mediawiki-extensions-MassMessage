<?php
declare( strict_types = 1 );

namespace MediaWiki\MassMessage\Api;

use MediaWiki\Api\ApiQuery;
use MediaWiki\Api\ApiQueryBase;
use MediaWiki\MassMessage\Content\MassMessageListContent;
use MediaWiki\Page\WikiPageFactory;
use MediaWiki\Title\Title;

/**
 * API module to retrieve the content of a mass message distribution list
 *
 * @ingroup API
 */
class ApiQueryMMContent extends ApiQueryBase {

	public function __construct(
		ApiQuery $query,
		string $moduleName,
		private readonly WikiPageFactory $wikiPageFactory,
	) {
		parent::__construct( $query, $moduleName, '' );
	}

	public function execute() {
		$pageSet = $this->getPageSet();
		$pageids = array_keys( $pageSet->getGoodPages() );
		if ( !$pageids ) {
			return;
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

		foreach ( $spamlists as $pageid => $spamlist ) {
			$content = $this->wikiPageFactory->newFromTitle( $spamlist )->getContent();
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
	}

	/**
	 * @param array $params
	 * @return string
	 */
	public function getCacheMode( $params ) {
		return 'public';
	}

	/** @inheritDoc */
	public function getAllowedParams() {
		return [];
	}

	/** @inheritDoc */
	protected function getExamplesMessages() {
		return [
			'action=query&prop=info|mmcontent&titles=Spam%20list'
				=> 'apihelp-query+mmcontent-example-1',
		];
	}
}

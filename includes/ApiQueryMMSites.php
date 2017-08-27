<?php
/**
 * API module to serve autocomplete requests for the site field in MassMessage
 *
 * @ingroup API
 */
class ApiQueryMMSites extends ApiQueryBase {

	public function execute() {
		$params = $this->extractRequestParams();
		$term = strtolower( $params['term'] );

		$sites = array_keys( MassMessage::getDatabases() );
		sort( $sites );
		$matches = [];
		foreach ( $sites as $site ) {
			if ( strpos( $site, $term ) === 0 ) {
				$matches[] = $site;
				if ( count( $matches ) >= 10 ) {
					break;
				}
			}
		}

		$result = $this->getResult();
		$result->setIndexedTagName( $matches, 'site' );
		$result->addValue( 'query', $this->getModuleName(), $matches );
	}

	public function getCacheMode( $params ) {
		return 'public';
	}

	public function getAllowedParams() {
		return [
			'term' => [
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_REQUIRED => true
			]
		];
	}

	public function isInternal() {
		return true;
	}

	/**
	 * @see ApiBase::getExamplesMessages()
	 * @return array
	 */
	protected function getExamplesMessages() {
		return [
			'action=query&list=mmsites&term=en'
				=> 'apihelp-query+mmsites-example-1',
		];
	}
}

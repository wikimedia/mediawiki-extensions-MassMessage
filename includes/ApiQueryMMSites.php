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
		$matches = array();
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
		return array(
			'term' => array(
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_REQUIRED => true
			)
		);
	}

	/**
	 * @deprecated since MediaWiki core 1.25
	 */
	public function getParamDescription() {
		return array(
			'term' => 'The prefix to search for'
		);
	}

	public function isInternal() {
		return true;
	}

	/**
	 * @deprecated since MediaWiki core 1.25
	 */
	public function getDescription() {
		return 'Serve autocomplete requests for the site field in MassMessage';
	}

	/**
	 * @deprecated since MediaWiki core 1.25
	 */
	public function getExamples() {
		return array(
			'api.php?action=query&list=mmsites&term=en'
		);
	}

	/**
	 * @see ApiBase::getExamplesMessages()
	 */
	protected function getExamplesMessages() {
		return array(
			'action=query&list=mmsites&term=en'
				=> 'apihelp-query+mmsites-example-1',
		);
	}
}
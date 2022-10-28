<?php

namespace MediaWiki\MassMessage\Api;

use ApiQueryBase;
use MediaWiki\MassMessage\Lookup\DatabaseLookup;
use Wikimedia\ParamValidator\ParamValidator;

/**
 * API module to serve autocomplete requests for the site field in MassMessage.
 *
 * @ingroup API
 */

class ApiQueryMMSites extends ApiQueryBase {

	public function execute() {
		$params = $this->extractRequestParams();
		$term = strtolower( $params['term'] );

		$sites = array_keys( DatabaseLookup::getDatabases() );
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

	/**
	 * @param array $params
	 * @return string
	 */
	public function getCacheMode( $params ) {
		return 'public';
	}

	/** @inheritDoc */
	public function getAllowedParams() {
		return [
			'term' => [
				ParamValidator::PARAM_TYPE => 'string',
				ParamValidator::PARAM_REQUIRED => true
			]
		];
	}

	public function isInternal() {
		return true;
	}

	/** @inheritDoc */
	protected function getExamplesMessages() {
		return [
			'action=query&list=mmsites&term=en'
				=> 'apihelp-query+mmsites-example-1',
		];
	}
}

<?php

namespace MediaWiki\MassMessage\Lookup;

use MediaWiki\MassMessage\UrlHelper;
use MediaWiki\MediaWikiServices;
use MediaWiki\WikiMap\WikiMap;

class DatabaseLookup {

	/**
	 * Get a mapping from site domains to database names.
	 * Requires $wgConf to be set up properly.
	 * Tries to read from cache if possible.
	 *
	 * @return array
	 */
	public static function getDatabases() {
		$cache = MediaWikiServices::getInstance()->getMainWANObjectCache();

		return $cache->getWithSetCallback(
			$cache->makeGlobalKey( 'massmessage', 'urltodb' ),
			$cache::TTL_HOUR,
			static function () {
				global $wgConf;

				$dbs = $wgConf->getLocalDatabases();
				$mapping = [];
				foreach ( $dbs as $dbname ) {
					$url = WikiMap::getWiki( $dbname )->getCanonicalServer();
					$site = UrlHelper::getBaseUrl( $url );
					$mapping[$site] = $dbname;
				}
				return $mapping;
			}
		);
	}

	/**
	 * Get database name from URL hostname or null if nothing is found.
	 *
	 * @param string $host
	 * @return string|null
	 */
	public static function getDBName( $host ) {
		$configuredAliases = MediaWikiServices::getInstance()->getMainConfig()->get( 'MassMessageWikiAliases' );
		$mapping = self::getDatabases();
		return $mapping[$host] ?? $configuredAliases[$host] ?? null;
	}
}

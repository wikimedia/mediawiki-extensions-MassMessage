<?php
namespace MediaWiki\MassMessage;

use WikiMap;
use MediaWiki\MediaWikiServices;

class DatabaseLookup {

	/**
	 * Get a mapping from site domains to database names
	 * Requires $wgConf to be set up properly
	 * Tries to read from cache if possible
	 * @return array
	 */
	public static function getDatabases() {
		static $mapping = null;
		if ( $mapping === null ) {
			$cache = MediaWikiServices::getInstance()->getMainWANObjectCache();

			$mapping = $cache->getWithSetCallback(
				$cache->makeGlobalKey( 'massmessage', 'urltodb' ),
				$cache::TTL_HOUR,
				function () {
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

		return $mapping;
	}

	/**
	 * Get database name from URL hostname
	 * @param string $host
	 * @return string
	 */
	public static function getDBName( $host ) {
		global $wgMassMessageWikiAliases;
		$mapping = self::getDatabases();
		if ( isset( $mapping[$host] ) ) {
			return $mapping[$host];
		}
		if ( isset( $wgMassMessageWikiAliases[$host] ) ) {
			return $wgMassMessageWikiAliases[$host];
		}
		return null; // Couldn't find anything
	}

}

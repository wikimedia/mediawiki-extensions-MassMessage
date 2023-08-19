<?php

namespace MediaWiki\MassMessage;

use MediaWiki\MediaWikiServices;
use MediaWiki\Title\Title;

class UrlHelper {

	/**
	 * Function to follow redirects.
	 *
	 * @param Title $title
	 * @return Title|null null if the page is an interwiki redirect
	 */
	public static function followRedirect( Title $title ) {
		if ( !$title->isRedirect() ) {
			return $title;
		}
		$wikipage = MediaWikiServices::getInstance()->getWikiPageFactory()->newFromTitle( $title );

		$target = $wikipage->followRedirect();
		if ( $target instanceof Title ) {
			return $target;
		}

		// This must be an interwiki redirect
		return null;
	}

	/**
	 * Returns the basic hostname and port using wfParseUrl.
	 *
	 * @param string $url
	 * @return string
	 */
	public static function getBaseUrl( $url ) {
		static $mapping = [];

		if ( isset( $mapping[$url] ) ) {
			return $mapping[$url];
		}

		$parse = wfParseUrl( $url );
		$mapping[$url] = $parse['host'];
		if ( isset( $parse['port'] ) ) {
			$mapping[$url] .= ':' . $parse['port'];
		}
		return $mapping[$url];
	}
}

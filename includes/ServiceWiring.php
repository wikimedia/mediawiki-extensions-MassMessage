<?php
/**
 * List of services in this extension with construction instructions.
 *
 * @file
 * @author Abijeet Patro
 * @license GPL-2.0-or-later
 * @since 2022.01
 */

declare( strict_types = 1 );

use MediaWiki\MassMessage\MessageContentFetcher\LocalMessageContentFetcher;
use MediaWiki\MassMessage\MessageContentFetcher\RemoteMessageContentFetcher;
use MediaWiki\MediaWikiServices;

/** @phpcs-require-sorted-array */
return [
	'MassMessage:LocalMessageContentFetcher' => static function (
		MediaWikiServices $services
	): LocalMessageContentFetcher {
		return new LocalMessageContentFetcher( $services->getRevisionStore() );
	},

	'MassMessage:RemoteMessageContentFetcher' => static function (
		MediaWikiServices $services
	): RemoteMessageContentFetcher {
		$config = $services->getMainConfig();
		$siteConfiguration = $config->get( 'Conf' );

		return new RemoteMessageContentFetcher(
			$services->getHttpRequestFactory(),
			$siteConfiguration
		);
	},
];

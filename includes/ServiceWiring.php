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

use MediaWiki\MassMessage\MessageContentFetcher\LabeledSectionContentFetcher;
use MediaWiki\MassMessage\MessageContentFetcher\LocalMessageContentFetcher;
use MediaWiki\MassMessage\MessageContentFetcher\RemoteMessageContentFetcher;
use MediaWiki\MassMessage\PageMessage\PageMessageBuilder;
use MediaWiki\MediaWikiServices;
use MediaWiki\WikiMap\WikiMap;

/** @phpcs-require-sorted-array */
return [
	'MassMessage:LabeledSectionContentFetcher' => static function (): LabeledSectionContentFetcher {
		return new LabeledSectionContentFetcher();
	},

	'MassMessage:LocalMessageContentFetcher' => static function (
		MediaWikiServices $services
	): LocalMessageContentFetcher {
		return new LocalMessageContentFetcher( $services->getRevisionStore() );
	},

	'MassMessage:PageMessageBuilder' => static function ( MediaWikiServices $services ): PageMessageBuilder {
		return new PageMessageBuilder(
			$services->get( 'MassMessage:LocalMessageContentFetcher' ),
			$services->get( 'MassMessage:LabeledSectionContentFetcher' ),
			$services->get( 'MassMessage:RemoteMessageContentFetcher' ),
			$services->getLanguageNameUtils(),
			$services->getLanguageFallback(),
			WikiMap::getCurrentWikiId()
		);
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

<?php
declare( strict_types = 1 );

namespace MediaWiki\MassMessage;

use MediaWiki\MassMessage\MessageContentFetcher\LabeledSectionContentFetcher;
use MediaWiki\MassMessage\MessageContentFetcher\LocalMessageContentFetcher;
use MediaWiki\MassMessage\MessageContentFetcher\RemoteMessageContentFetcher;
use MediaWiki\MassMessage\PageMessage\PageMessageBuilder;
use MediaWiki\MediaWikiServices;
use Psr\Container\ContainerInterface;

/**
 * Minimal service container.
 *
 * Main purpose is to give type-hinted getters for services defined in this extensions.
 * @author Abijeet Patro
 * @since 2022.01
 * @license GPL-2.0-or-later
 */
class Services implements ContainerInterface {
	/** @var MediaWikiServices */
	private $container;

	/** @param MediaWikiServices $container */
	private function __construct( MediaWikiServices $container ) {
		$this->container = $container;
	}

	/** @return Services */
	public static function getInstance(): Services {
		return new self( MediaWikiServices::getInstance() );
	}

	/** @inheritDoc */
	public function get( string $id ) {
		return $this->container->get( $id );
	}

	/** @inheritDoc */
	public function has( string $id ): bool {
		return $this->container->has( $id );
	}

	/**
	 * @since 2022.01
	 * @return LabeledSectionContentFetcher
	 */
	public function getLabeledSectionContentFetcher(): LabeledSectionContentFetcher {
		return $this->container->getService( 'MassMessage:LabeledSectionContentFetcher' );
	}

	/**
	 * @since 2022.01
	 * @return LocalMessageContentFetcher
	 */
	public function getLocalMessageContentFetcher(): LocalMessageContentFetcher {
		return $this->container->getService( 'MassMessage:LocalMessageContentFetcher' );
	}

	/**
	 * @since 2022.01
	 * @return PageMessageBuilder
	 */
	public function getPageMessageBuilder(): PageMessageBuilder {
		return $this->container->getService( 'MassMessage:PageMessageBuilder' );
	}

	/**
	 * @since 2022.01
	 * @return RemoteMessageContentFetcher
	 */
	public function getRemoteMessageContentFetcher(): RemoteMessageContentFetcher {
		return $this->container->getService( 'MassMessage:RemoteMessageContentFetcher' );
	}
}

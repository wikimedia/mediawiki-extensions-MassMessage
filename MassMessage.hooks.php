<?php

/**
 * Hooks!
 */

class MassMessageHooks {

	/**
	 * Hook to load our parser function
	 * @param  Parser $parser
	 * @return bool
	 */
	public static function onParserFirstCallInit( Parser &$parser ) {
		$parser->setFunctionHook( 'target', 'MassMessageHooks::ParserFunction' );

		return true;
	}

	/**
	 * Parser function for {{#target:User talk:Example|en.wikipedia.org}}
	 * Hostname is optional for local delivery
	 * @param Parser $parser
	 * @param string $site
	 * @param string $page
	 * @return array
	 */
	public static function ParserFunction( $parser, $page, $site = '' ) {
		global $wgScript;
		$data = array( 'site' => $site, 'title' => $page );
		if ( trim( $site ) === '' ) {
			// Assume it's a local delivery
			global $wgServer, $wgDBname;
			$site = MassMessage::getBaseUrl( $wgServer );
			$data['site'] = $site;
			$data['dbname'] = $wgDBname;
		}
		// Use a message so wikis can customize the output
		$msg = wfMessage( 'massmessage-target' )->params( $site, $wgScript, $page )->plain();
		$output = $parser->getOutput();

		// Store the data in case we're parsing it manually
		if ( defined( 'MASSMESSAGE_PARSE' ) ) {
			if ( !$output->getProperty( 'massmessage-targets' ) ) {
				$output->setProperty( 'massmessage-targets', serialize( array( $data ) ) );
			} else {
				$output->setProperty( 'massmessage-targets' , serialize( array_merge( unserialize( $output->getProperty( 'massmessage-targets' ) ),  array( $data ) ) ) );
			}
		}

		return array( $msg, 'noparse' => false );
	}

	/**
	 * Add a row with the number of queued messages to Special:Statistics
	 * @param  array $extraStats
	 * @return bool
	 */
	public static function onSpecialStatsAddExtra( &$extraStats ) {
		// from runJobs.php --group
		$group = JobQueueGroup::singleton();
		$queue = $group->get( 'massmessageJob' );
		$pending = $queue->getSize();
		$claimed = $queue->getAcquiredCount();
		$abandoned = $queue->getAbandonedCount();
		$active = ( $claimed - $abandoned );

		$queued = $active + $pending;
		$extraStats['massmessage-queued-count'] = $queued;

		return true;
	}

	/**
	 * Load our unit tests
	 */
	public static function onUnitTestsList( &$files ) {
		$files += glob( __DIR__ . '/tests/*Test.php' );

		return true;
	}
}

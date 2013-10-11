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
		$parser->setFunctionHook( 'target', 'MassMessageHooks::outputParserFunction' );
		return true;
	}

	/**
	 * Verifies the user submitted data to check it's valid
	 * @param string $page
	 * @param string $site
	 * @return array
	 */
	public static function verifyPFData( $page, $site ) {
		global $wgServer, $wgAllowGlobalMessaging;
		$data = array( 'site' => $site, 'title' => $page );
		if ( trim( $site ) === '' ) {
			$site = MassMessage::getBaseUrl( $wgServer );
			$data['site'] = $site;
			$data['wiki'] = wfWikiID();
		} elseif ( filter_var( 'http://' . $site, FILTER_VALIDATE_URL ) === false ) {
			// Try and see if the site provided is not valid
			// We can just prefix http:// in front since it needs some kind of protocol
			return MassMessage::parserError( 'massmessage-parse-badurl', $site );
		}
		if ( is_null( Title::newFromText( $page ) ) ) {
			// Check if the page provided is not valid
			return MassMessage::parserError( 'massmessage-parse-badpage', $page );
		}
		if ( !isset( $data['wiki'] ) ) {
			$data['wiki'] = MassMessage::getDBName( $data['site'] );
			if ( $data['wiki'] === null ) {
				return MassMessage::parserError( 'massmessage-parse-badurl', $site );
			}
		}
		if ( !$wgAllowGlobalMessaging && $data['wiki'] != wfWikiID() ) {
			return MassMessage::parserError( 'massmessage-global-disallowed' );
		}
		return $data;
	}

	/**
	 * Main parser function for {{#target:User talk:Example|en.wikipedia.org}}
	 * Prepares the human facing output
	 * Hostname is optional for local delivery
	 * @param Parser $parser
	 * @param string $site
	 * @param string $page
	 * @return array
	 */
	public static function outputParserFunction( $parser, $page, $site = '' ) {
		global $wgScript;

		$data = self::verifyPFData( $page, $site );
		if ( isset( $data['error'] ) ) {
			return $data;
		}

		$site = $data['site'];
		$page = $data['title'];

		// Use a message so wikis can customize the output
		$msg = wfMessage( 'massmessage-target' )->params( $site, $wgScript, $page )->plain();

		return array( $msg, 'noparse' => false );
	}

	/**
	 * Reads the parser function and extracts the data from it
	 * @param Parser $parser
	 * @param string $page
	 * @param string $site
	 * @return string
	 */
	public static function storeDataParserFunction( $parser, $page, $site = '' ) {
		$data = self::verifyPFData( $page, $site );
		if ( isset( $data['error'] ) ) {
			return ''; // Output doesn't matter
		}
		$output = $parser->getOutput();
		$current = $output->getProperty( 'massmessage-targets' );
		if ( !$current ) {
			$output->setProperty( 'massmessage-targets', serialize( array( $data ) ) );
		} else {
			$output->setProperty( 'massmessage-targets' , serialize(
				array_merge( unserialize( $current ),  array( $data ) ) ) );
		}
		return '';
	}

	/**
	 * Add our username to the list of reserved ones
	 * @param $reservedUsernames array
	 * @return bool
	 */
	public static function onUserGetReservedNames( &$reservedUsernames ) {
		global $wgMassMessageAccountUsername;
		$reservedUsernames[] = $wgMassMessageAccountUsername;
		return true;
	}

	/**
	 * If someone is trying to rename the bot, don't let them.
	 * @param $uid int
	 * @param $oldName string
	 * @param $newName string
	 * @return bool|string
	 */
	public static function onRenameUserPreRename( $uid, $oldName, $newName ) {
		global $wgMassMessageAccountUsername;
		if ( $oldName == $wgMassMessageAccountUsername ) {
			return wfMessage( 'massmessage-cannot-rename' )->text() ;
		}
		return true;
	}

	/**
	 * Add a row with the number of queued messages to Special:Statistics
	 * @param  array $extraStats
	 * @return bool
	 */
	public static function onSpecialStatsAddExtra( &$extraStats ) {
		$extraStats['massmessage-queued-count'] = MassMessage::getQueuedCount();
		return true;
	}

	/**
	 * Add the number of queued messages to &meta=siteinfo&siprop=statistics
	 * @param $result array
	 * @return bool
	 */
	public static function onAPIQuerySiteInfoStatisticsInfo( &$result ) {
		$result['queued-massmessages'] = MassMessage::getQueuedCount();
		return true;
	}

	/**
	 * Load our unit tests
	 */
	public static function onUnitTestsList( &$files ) {
		$files += glob( __DIR__ . '/tests/*Test.php' );

		return true;
	}

	/**
	 * Echo!
	 *
	 * @param $event EchoEvent
	 * @return bool
	 */
	public static function onBeforeEchoEventInsert( $event ) {
		// Don't spam a user with mention notifications if it's a MassMessage
		if ( $event->getType() == 'mention' && $event->getAgent() && // getAgent() can return null
			$event->getAgent()->getId() == MassMessage::getMessengerUser()->getId() ) {
			return false;
		}

		return true;
	}

}

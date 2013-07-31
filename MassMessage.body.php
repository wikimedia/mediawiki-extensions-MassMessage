<?php

/**
 * Some core functions needed by the ex.
 * Based on code from AbuseFilter
 * https://mediawiki.org/wiki/Extension:AbuseFilter
 *
 * @file
 * @author Kunal Mehta
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */



class MassMessage {

	/**
	 * A mapping of hostname to database name
	 * @var array
	 */
	protected $dbnames = array();

	/**
	 * Function to follow redirects
	 *
	 * @param $title Title
	 * @return Title|null null if the page is an interwiki redirect
	 */
	public static function followRedirect( $title ) {
		if ( !$title->isRedirect() ) {
			return $title;
		}
		$wikipage = WikiPage::factory( $title );

		$target = $wikipage->followRedirect();
		if ( $target instanceof Title ) {
			return $target;
		} else {
			return null; // Interwiki redirect
		}
	}


	/**
	 * Sets up the messenger account for our use if it hasn't been already.
	 *
	 * @return User
	 * @fixme This should use the langage for the target site, not submission site
	 */
	public static function getMessengerUser() {
		// Function kinda copied from the AbuseFilter
		$user = User::newFromName( wfMessage( 'massmessage-sender' )->inContentLanguage()->text() );
		$user->load();
		if ( $user->getId() && $user->mPassword == '' ) {
			// We've already stolen the account
			return $user;
		}

		if ( !$user->getId() ) {
			$user->addToDatabase();
			$user->saveSettings();

			// Increment site_stats.ss_users
			$ssu = new SiteStatsUpdate( 0, 0, 0, 0, 1 );
			$ssu->doUpdate();
		} else {
			// Someone already created the account, lets take it over.
			$user->setPassword( null );
			$user->setEmail( null );
			$user->saveSettings();
		}

		// Make the user a bot so it doesn't look weird
		$user->addGroup( 'bot' );

		return $user;
	}
	/**
	 * Returns the basic hostname and port using wfParseUrl
	 * @param  string $url URL to parse
	 * @return string
	 */
	public static function getBaseUrl( $url ) {
		$parse = wfParseUrl( $url );
		$site = $parse['host'];
		if ( isset( $parse['port'] ) ) {
			$site .= ':' . $parse['port'];
		}
		return $site;
	}

	/**
	 * Get database name from URL hostname
	 * Requires Extension:SiteMatrix. If not available will return null.
	 * @param  string $host
	 * @return string
	 */
	public static function getDBName( $host ) {
		if ( isset( $this->dbnames[$host] ) ) {
			return $this->dbnames[$host];
		}
		if ( !class_exists( 'SiteMatrix' ) ) {
			return null;
		}
		$matrix = new SiteMatrix();
		foreach ( $matrix->hosts as $dbname => $url ) {
			$parse = wfParseUrl( $url );
			if ( $parse['host'] == $host ) {
				$this->dbnames[$host] = $dbname; // Store it for later
				return $dbname;
			}
		}
		return null; // Couldn't find anything
	}

	/**
	 * Normalizes an array of page/site combos
	 * Also removes some dupes
	 * @param  array $pages
	 * @param  bool $isLocal
	 * @return array
	 * @fixme Follow redirects on other sites
	 */
	public static function normalizeSpamList( $pages, $isLocal ) {
		global $wgDBname;
		$data = array();
		foreach ( $pages as $page ) {
			if ( $isLocal ) {
				$title = Title::newFromText( $page['title'] );
				$title = self::followRedirect( $title );
				if ( $title == null ) {
					continue; // Interwiki redirect
				}
				$page['title'] = $title->getFullText();
			}
			if ( !isset( $page['dbname'] ) ) {
				$dbname = self::getDBName( $page['site'] );
				if ( $dbname == null ) { // Not in the site matrix?
					continue;
				}
				$page['dbname'] = $dbname;
			}
			// Use an assoc array to clear dupes
			if ( $page['dbname'] == $wgDBname || !$isLocal ) {
				// If the delivery is local, only allow requests on the same site.
				$data[$page['title'] . $page['site']] = $page;
			}

		}

		return $data;
	}

	/**
	 * Get an array of targets via the #target parser function
	 * @param  Title $spamlist
	 * @param  IContextSource $context
	 * @return array
	 */
	public static function getParserFunctionTargets( $spamlist, $context ) {
		$page = WikiPage::factory( $spamlist );
		$content = $page->getContent( Revision::RAW );
		if ( $content instanceof TextContent ) {
			$text = $content->getNativeData();
		} else {
			// Spamlist input isn't a text page
			// @fixme
			// $this->status->fatal( 'massmessage-spamlist-doesnotexist' );
			return array();
		}

		// Prep the parser
		define( 'MASSMESSAGE_PARSE', true );
		$article = Article::newFromTitle( $spamlist, $context );
		$parserOptions = $article->makeParserOptions( $article->getContext() );
		$parser = new Parser();

		// Parse
		$output = $parser->parse( $text, $spamlist, $parserOptions );
		$data = $output->getProperty( 'massmessage-targets' );

		if ( $data ) {
			return $data;
		} else {
			return array();  // No parser functions on page
		}

	}

}

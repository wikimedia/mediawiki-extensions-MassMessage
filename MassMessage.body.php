<?php

/*
 * Some core functions needed by the ex.
 * Based on code from AbuseFilter
 * https://mediawiki.org/wiki/Extension:AbuseFilter
 *
 * @file
 * @author Kunal Mehta
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */



class MassMessage {

	/*
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
}
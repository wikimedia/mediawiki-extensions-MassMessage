<?php

/**
 * JobQueue class for jobs queued server side
 *
 * @file
 * @ingroup JobQueue
 * @author Kunal Mehta
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */
class MassMessageServerSideJob extends MassMessageJob {
	public function __construct( Title $title, array $params, $id = 0 ) {
		Job::__construct( 'MassMessageServerSideJob', $title, $params, $id );
	}

	/**
	 * Can't opt-out of these messages!
	 *
	 * @param Title $title
	 * @return bool
	 */
	public function isOptedOut( Title $title ) {
		return false;
	}

	/**
	 * Don't add any hidden comments
	 *
	 * @return string
	 */
	function makeText() {
		return $this->params['message'];
	}
}

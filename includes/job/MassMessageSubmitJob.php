<?php
namespace MediaWiki\MassMessage;

use Title;
use Job;
use JobQueueGroup;

/**
 * JobQueue class to queue other jobs
 *
 *
 * @file
 * @ingroup JobQueue
 * @author Kunal Mehta
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

class MassMessageSubmitJob extends Job {
	public function __construct( Title $title, array $params, $id = 0 ) {
		// Back-compat
		if ( !isset( $params['class'] ) ) {
			$params['class'] = MassMessageJob::class;
		}
		parent::__construct( 'MassMessageSubmitJob', $title, $params, $id );
	}

	/**
	 * Queue some more jobs!
	 *
	 * @return bool
	 */
	public function run() {
		$jobsByTarget = $this->getJobs();

		foreach ( $jobsByTarget as $wiki => $jobs ) {
			JobQueueGroup::singleton( $wiki )->push( $jobs );
		}

		return true;
	}

	/**
	 * @return \Job[][]
	 */
	public function getJobs() {
		$data = $this->params['data'];
		$pages = $this->params['pages'];
		$class = $this->params['class'];
		$jobsByTarget = [];

		foreach ( $pages as $page ) {
			$title = Title::newFromText( $page['title'] );
			// Store the title as plain text to avoid namespace/interwiki prefix
			// collisions, see bug 57464 and 58524
			$data['title'] = $page['title'];
			$jobsByTarget[$page['wiki']][] = new $class( $title, $data );
		}

		return $jobsByTarget;
	}
}

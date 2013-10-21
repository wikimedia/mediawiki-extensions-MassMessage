<?php
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
		parent::__construct( 'MassMessageSubmitJob', $title, $params, $id );
	}

	/**
	 * Queue some more jobs!
	 *
	 * @return bool
	 */
	public function run() {
		$data = $this->params['data'];
		$pages = $this->params['pages'];
		$jobsByTarget = array();

		foreach ( $pages as $page ) {
			$title = Title::newFromText( $page['title'] );
			$jobsByTarget[$page['wiki']][] = new MassMessageJob( $title, $data );
		}

		foreach ( $jobsByTarget as $wiki => $jobs ) {
			JobQueueGroup::singleton( $wiki )->push( $jobs );
		}

		return true;
	}

}

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
	public function __construct( $title, $params, $id = 0 ) {
		parent::__construct( 'massmessagesubmitJob', $title, $params, $id );
	}

	/**
	 * Queue some more jobs!
	 *
	 * @return bool
	 */
	public function run() {
		$data = $this->params['data'];
		$pages = $this->params['pages'];
		foreach ( $pages as $page ) {
			$title = Title::newFromText( $page['title'] );
			$job = new MassMessageJob( $title, $data );
			JobQueueGroup::singleton( $page['wiki'] )->push( $job );
		}

		return true;
	}

}

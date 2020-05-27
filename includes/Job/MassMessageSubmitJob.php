<?php

namespace MediaWiki\MassMessage\Job;

use Job;
use JobQueueGroup;
use MWTimestamp;
use Title;

/**
 * JobQueue class to queue other jobs.
 *
 * @file
 * @ingroup JobQueue
 * @author Kunal Mehta
 * @license GPL-2.0-or-later
 */

class MassMessageSubmitJob extends Job {
	public function __construct( Title $title, array $params ) {
		// Back-compat
		if ( !isset( $params['class'] ) ) {
			$params['class'] = MassMessageJob::class;
		}
		if ( !isset( $params['timestamp'] ) ) {
			$params['timestamp'] = MWTimestamp::now();
		}
		parent::__construct( 'MassMessageSubmitJob', $title, $params );
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
			// collisions, see tasks T59464 and T60524
			$data['title'] = $page['title'];
			// We want to deduplicate individual messages based on retries of the
			// batch submit job if they happen
			$data['rootJobSignature'] = sha1( json_encode( $this->getDeduplicationInfo() ) );
			$data['rootJobTimestamp'] = $this->params['timestamp'];
			$jobsByTarget[$page['wiki']][] = new $class( $title, $data );
		}

		return $jobsByTarget;
	}
}

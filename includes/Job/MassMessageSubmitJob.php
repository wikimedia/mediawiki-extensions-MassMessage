<?php

namespace MediaWiki\MassMessage\Job;

use Job;
use MediaWiki\MediaWikiServices;
use MediaWiki\Title\Title;
use MediaWiki\Utils\MWTimestamp;

/**
 * JobQueue class to queue other jobs.
 *
 * @file
 * @ingroup JobQueue
 * @author Kunal Mehta
 * @license GPL-2.0-or-later
 */

class MassMessageSubmitJob extends Job {
	/**
	 * @param Title $title
	 * @param array $params
	 */
	public function __construct( Title $title, array $params ) {
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

		$jobQueueGroupFactory = MediaWikiServices::getInstance()->getJobQueueGroupFactory();
		foreach ( $jobsByTarget as $wiki => $jobs ) {
			$jobQueueGroupFactory->makeJobQueueGroup( $wiki )->push( $jobs );
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

		// We want to deduplicate individual messages based on retries of the
		// batch submit job if they happen
		$data['rootJobSignature'] = sha1( json_encode( $this->getDeduplicationInfo() ) );
		$data['rootJobTimestamp'] = $this->params['timestamp'];

		$jobsByTarget = [];
		foreach ( $pages as $page ) {
			$title = Title::newFromText( $page['title'] );
			// Store the title as plain text to avoid namespace/interwiki prefix
			// collisions, see tasks T59464 and T60524
			$data['title'] = $page['title'];
			$jobsByTarget[$page['wiki']][] = new $class( $title, $data );
		}

		return $jobsByTarget;
	}
}

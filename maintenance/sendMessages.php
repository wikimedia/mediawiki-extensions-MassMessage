<?php

namespace MediaWiki\MassMessage;

use MediaWiki\Maintenance\Maintenance;
use MediaWiki\MassMessage\Job\MassMessageServerSideJob;
use MediaWiki\MassMessage\Job\MassMessageSubmitJob;
use MediaWiki\Title\Title;
use MediaWiki\WikiMap\WikiMap;

$IP = getenv( 'MW_INSTALL_PATH' );
if ( $IP === false ) {
	$IP = __DIR__ . '/../../..';
}
require_once "$IP/maintenance/Maintenance.php";

/**
 * Script to send MassMessages server-side
 *
 * Expects a page list formatted as a .tsv file, with "PageName<tab>WikiId" on each line.
 * Subject line and message body are also stored as files.
 */
class SendMessages extends Maintenance {
	public function __construct() {
		parent::__construct();
		$this->addOption( 'pagelist', 'Name of file with a list of pages to send to in it', true, true );
		$this->addOption( 'subject', 'Name of file with the subject in it', true, true );
		$this->addOption( 'message', 'Name of file with the message body in it', true, true );
		$this->requireExtension( 'MassMessage' );
	}

	public function execute() {
		$info = [];

		foreach ( [ 'pagelist', 'subject', 'message' ] as $arg ) {
			$option = $this->getOption( $arg );
			if ( !is_file( $this->getOption( $arg ) ) ) {
				$this->fatalError( "Required argument $arg was passed an invalid filename.\n" );
			}

			// Also include check if the file size before even reading the file
			if ( $arg !== 'pagelist' && filesize( $this->getOption( $arg ) ) !== 0 ) {
				$contents = file_get_contents( $option );
				if ( $contents !== false ) {
					$info[$arg] = trim( $contents );
				} else {
					$this->fatalError( "Unable to read $option.\n" );
				}
			} else {
				$this->fatalError( "$option is empty, must have some content.\n" );
			}
		}

		$list = $this->getOption( 'pagelist' );
		if ( filesize( $this->getOption( $arg ) ) !== 0 ) {
			$file = fopen( $list, 'r' );
			if ( $file === false ) {
				$this->fatalError( "Could not open pagelist file: \"$list\".\n" );
			}
		} else {
			$this->fatalError( "Error: $list is empty.\n" );
		}

		$pages = [];
		$this->output( "Reading from \"$list\".\n" );

		$lineNum = 0;
		// phpcs:ignore Generic.CodeAnalysis.AssignmentInCondition.FoundInWhileCondition
		while ( $line = trim( fgets( $file ) ) ) {
			$lineNum++;
			$exp = explode( "\t", $line );
			if ( count( $exp ) !== 2 ) {
				$this->fatalError( "Line $lineNum should have two components: $line" );
			}
			if ( !WikiMap::getWiki( $exp[1] ) ) {
				$this->fatalError( "Invalid wiki name on line $lineNum: " . $exp[1] );
			}
			$pages[] = [
				'title' => $exp[0],
				'wiki' => $exp[1],
			];
		}

		fclose( $file );

		// Submit the jobs
		$params = [
			'data' => $info,
			'pages' => $pages,
			'class' => MassMessageServerSideJob::class,
		];

		$submitJob = new MassMessageSubmitJob(
			Title::newFromText( 'SendMassMessages' ),
			$params
		);
		// Just insert the individual jobs into the queue now.
		$submitJob->run();
		$count = count( $pages );
		$this->output( "Queued $count jobs. Done!\n" );
	}
}

$maintClass = SendMessages::class;
require_once RUN_MAINTENANCE_IF_MAIN;

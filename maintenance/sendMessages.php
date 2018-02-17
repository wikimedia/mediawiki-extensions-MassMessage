<?php

namespace MediaWiki\MassMessage;

$IP = getenv( 'MW_INSTALL_PATH' );
if ( $IP === false ) {
	$IP = __DIR__ . '/../../..';
}
require_once "$IP/maintenance/Maintenance.php";

use Title;

/**
 * Script to send MassMessages server-side
 *
 * Excepts a page list formatted as a .tsv file, with "PageName\tWikiId" on each line
 * Subject line and message body are also stored as files
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
				$this->error( "Error: required argument $arg was passed an invalid filename.\n", 1 );
			}

			if ( $arg !== 'pagelist' ) {
				$contents = file_get_contents( $option );
				if ( $contents !== false ) {
					$info[$arg] = trim( $contents );
				} else {
					$this->error( "Error: Unable to read $option.\n", 1 );
				}
			}
		}

		$list = $this->getOption( 'pagelist' );
		$file = fopen( $list, 'r' );
		if ( $file === false ) {
			$this->error( "Error: could not open pagelist file: \"$list\".\n", 1 );
		}

		$pages = [];
		$this->output( "Reading from \"$list\".\n" );

		// @codingStandardsIgnoreStart
		while ( $line = trim( fgets( $file ) ) ) {
		// @codingStandardsIgnoreEnd
			$exp = explode( "\t", $line );
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
			'class' => 'MassMessageServerSideJob',
		];

		$submitJob = new MassMessageSubmitJob(
			Title::newFromText( 'SendMassMessages' ),
			$params
		);
		$submitJob->run(); // Just insert the individual jobs into the queue now.
		$count = count( $pages );
		$this->output( "Queued $count jobs. Done!\n" );
	}
}

$maintClass = SendMessages::class;
require_once RUN_MAINTENANCE_IF_MAIN;

<?php
declare( strict_types = 1 );

namespace MediaWiki\MassMessage\Stub;

use MediaWiki\Revision\RevisionRecord;
use MediaWiki\Revision\RevisionStore;
use PHPUnit\Framework\TestCase;
use TextContent;

/**
 * Returns a RevisionStore stub with a RevisonRecord stub
 * @author Abijeet Patro
 * @since 2022.01
 * @license GPL-2.0-or-later
 */
class RevisionStoreStubFactory extends TestCase {
	public function getWithText( string $textContent ) {
		$revisionRecordStub = $this->createStub( RevisionRecord::class );
		$revisionRecordStub->method( 'getContent' )
			->willReturn( new TextContent( $textContent ) );

		$revisionStoreStub = $this->createStub( RevisionStore::class );
		$revisionStoreStub->method( 'getRevisionByTitle' )
			->willReturn( $revisionRecordStub );

		return $revisionStoreStub;
	}

	public function getWithoutRevisionRecord() {
		$revisionRecordStub = $this->createStub( RevisionStore::class );
		$revisionRecordStub->method( 'getRevisionByTitle' )
			->willReturn( null );

		return $revisionRecordStub;
	}
}

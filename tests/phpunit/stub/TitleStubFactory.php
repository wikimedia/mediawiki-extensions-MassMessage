<?php
declare( strict_types = 1 );

namespace MediaWiki\MassMessage\Stub;

use Language;
use PHPUnit\Framework\TestCase;
use Title;

/**
 * Returns a Title stub with given page language
 * @author Abijeet Patro
 * @since 2022.01
 * @license GPL-2.0-or-later
 */
class TitleStubFactory extends TestCase {
	public function getExistingTitle( string $titleStr, string $languageCode, string $languageDir ) {
		return $this->getStub( $titleStr, true, $languageCode, $languageDir );
	}

	public function getNonExistingTitle( string $titleStr, string $languageCode, string $languageDir ) {
		return $this->getStub( $titleStr, false, $languageCode, $languageDir );
	}

	/**
	 * @param string $titleStr
	 * @param bool $exists
	 * @param string $languageCode
	 * @param string $languageDir
	 * @return void
	 */
	private function getStub( string $titleStr, bool $exists, string $languageCode, string $languageDir ) {
		$titleStub = $this->createStub( Title::class );
		$titleStub->method( 'exists' )
			->willReturn( $exists );

		$titleStub->method( 'getPrefixedText' )
			->willReturn( $titleStr );

		$languageStub = $this->createStub( Language::class );
		$languageStub->method( 'getCode' )
			->willReturn( $languageCode );

		$languageStub->method( 'getDir' )
			->willReturn( $languageDir );

		$titleStub->method( 'getPageLanguage' )
			->willReturn( $languageStub );

		return $titleStub;
	}
}

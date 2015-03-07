<?php

/**
 * Tests for the MassMessage extension...
 *
 * @group Database
 */

class MassMessageTest extends MassMessageTestCase {

	public static function provideGetDBName() {
		return array(
			array( 'en.wikipedia.org', 'enwiki' ),
			array( 'fr.wikipedia.org', 'frwiki' ),
			array( 'de.wikipedia.org', 'dewiki' ),
			array( 'not.a.wiki.known.to.us', null ),
		);
	}

	/**
	 * @covers MassMessage::getDBName
	 * @dataProvider provideGetDBName
	 * @param $url
	 * @param $expected
	 */
	public function testGetDBName( $url, $expected ) {
		$dbname = MassMessage::getDBName( $url );
		$this->assertEquals( $expected, $dbname );
	}

	/**
	 * First parameter is the raw url to parse, second is expected output
	 * @return array
	 */
	public static function provideGetBaseUrl() {
		return array(
			array( 'http://en.wikipedia.org', 'en.wikipedia.org' ),
			array( 'https://en.wikipedia.org/wiki/Blah', 'en.wikipedia.org' ),
			array( 'http://en.wikipedia.org:80/wiki/Blah', 'en.wikipedia.org:80' ),
			array( '//test.wikidata.org/wiki/User talk:Example', 'test.wikidata.org' ),
		);
	}

	/**
	 * @covers MassMessage::getBaseUrl
	 * @dataProvider provideGetBaseUrl
	 * @param  string $url      raw url to parse
	 * @param  string $expected expected value
	 */
	public function testGetBaseUrl( $url, $expected ) {
		$output = MassMessage::getBaseUrl( $url );
		$this->assertEquals( $expected, $output );
	}

	public static function provideGetMessengerUser() {
		return array(
			array( 'MessengerBot' ),
			array( 'EdwardsBot' ),
			array( 'Blah blah blah' ),
		);
	}

	/**
	 * @covers MassMessage::getMessengerUser
	 * @dataProvider provideGetMessengerUser
	 * @param $name
	 */
	public function testGetMessengerUser( $name ) {
		$this->setMwGlobals( 'wgMassMessageAccountUsername', $name );
		$user = MassMessage::getMessengerUser();
		$this->assertEquals( $name, $user->getName() );
		$this->assertTrue( in_array( 'bot', $user->getGroups() ) );
		$this->assertInstanceOf( 'InvalidPassword', $user->getPassword() );
	}

	/**
	 * @covers MassMessage::followRedirect
	 */
	public function testFollowRedirect() {
		$title = Title::newfromtext( 'R1' );
		self::updatePage( $title, '#REDIRECT [[R2]]' );
		$title2 = Title::newfromtext( 'R2' );
		self::updatePage( $title2, 'foo' );

		$this->assertEquals(
			$title2->getFullText(),
			MassMessage::followRedirect( $title )->getFullText()
		);
		$this->assertEquals(
			$title2->getFullText(),
			MassMessage::followRedirect( $title2 )->getFullText()
		);
	}
}

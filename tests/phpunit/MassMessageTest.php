<?php

namespace MediaWiki\MassMessage;

/**
 * Tests for the MassMessage extension...
 *
 * @group Database
 */
class MassMessageTest extends MassMessageTestCase {
	public static function provideGetMessengerUser() {
		return [
			[ 'MessengerBot' ],
			[ 'EdwardsBot' ],
			[ 'Blah blah blah' ],
		];
	}

	/**
	 * @covers \MediaWiki\MassMessage\MassMessage::getMessengerUser
	 * @dataProvider provideGetMessengerUser
	 * @param string $name
	 */
	public function testGetMessengerUser( $name ) {
		$this->setMwGlobals( 'wgMassMessageAccountUsername', $name );
		$user = MassMessage::getMessengerUser();
		$this->assertEquals( $name, $user->getName() );
		$this->assertTrue( in_array( 'bot', $user->getGroups() ) );
	}
}

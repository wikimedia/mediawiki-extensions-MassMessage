<?php

namespace MediaWiki\MassMessage;

/**
 * @group Database
 * @covers \MediaWiki\MassMessage\MassMessage
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
	 * @dataProvider provideGetMessengerUser
	 * @param string $name
	 */
	public function testGetMessengerUser( $name ) {
		$userGroupManager = $this->getServiceContainer()->getUserGroupManager();
		$this->overrideConfigValue( 'MassMessageAccountUsername', $name );
		$user = MassMessage::getMessengerUser();
		$this->assertEquals( $name, $user->getName() );
		$this->assertContains( 'bot', $userGroupManager->getUserGroups( $user ) );
	}
}

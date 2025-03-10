<?php

namespace MediaWiki\MassMessage;

use MediaWiki\MassMessage\Notifications\SuppressMassMessageNotificationsMiddleware;
use MediaWiki\Notification\Notification;
use MediaWiki\Notification\NotificationEnvelope;
use MediaWiki\Notification\NotificationsBatch;
use MediaWiki\Notification\RecipientSet;
use MediaWiki\Notification\Types\WikiNotification;
use MediaWiki\Page\PageIdentity;

/**
 * @covers MediaWiki\MassMessage\Notifications\SuppressMassMessageNotificationsMiddleware
 * @group Database
 */
class SuppressMassMessageNotificationsMiddlewareTest extends \MediaWikiIntegrationTestCase {

	public function testDoesntRemoveRegularNotifications(): void {
		$title = $this->createMock( PageIdentity::class );
		$user = MassMessage::getMessengerUser();
		$recipients = new RecipientSet( [] );
		$batch = new NotificationsBatch(
			// first one should be preserved
			new NotificationEnvelope( new Notification( 'test' ), $recipients ),
			// second one should be removed
			new NotificationEnvelope( new WikiNotification( 'mention', $title, $user ), $recipients ),
			// third one should be preserved
			new NotificationEnvelope( new WikiNotification( 'some', $title, $user ), $recipients )
		);
		$sut = new SuppressMassMessageNotificationsMiddleware();
		$wasCalled = false;
		$sut->handle( $batch, static function () use ( &$wasCalled ) {
			$wasCalled = true;
		} );
		$this->assertTrue( $wasCalled, 'The next() was called' );
		$this->assertCount( 2, $batch );
		/** @var NotificationEnvelope[] $envelopes */
		$envelopes = iterator_to_array( $batch );

		$this->assertEquals( 'test', $envelopes[0]->getNotification()->getType() );
		$this->assertEquals( 'some', $envelopes[1]->getNotification()->getType() );
	}

}

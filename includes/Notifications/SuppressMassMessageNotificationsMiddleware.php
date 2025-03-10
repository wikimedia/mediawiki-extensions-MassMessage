<?php

namespace MediaWiki\MassMessage\Notifications;

use MediaWiki\MassMessage\MassMessage;
use MediaWiki\Notification\AgentAware;
use MediaWiki\Notification\Middleware\FilterMiddleware;
use MediaWiki\Notification\Middleware\FilterMiddlewareAction;
use MediaWiki\Notification\Notification;
use MediaWiki\Notification\NotificationEnvelope;
use MediaWiki\User\User;

/**
 * @since 1.44
 * @unstable
 */
class SuppressMassMessageNotificationsMiddleware extends FilterMiddleware {

	private ?User $messengerUser = null;

	private function getMessengerUser(): User {
		if ( $this->messengerUser === null ) {
			$this->messengerUser = MassMessage::getMessengerUser();
		}
		return $this->messengerUser;
	}

	/**
	 * @param NotificationEnvelope<Notification> $envelope
	 * @return FilterMiddlewareAction
	 */
	protected function filter( NotificationEnvelope $envelope ): FilterMiddlewareAction {
		$notification = $envelope->getNotification();
		$supportedNotificationTypes = [ 'mention', 'flow-mention' ];
		// we're interested only in notifications mention and flow-mention notifications
		if ( $notification instanceof AgentAware
			&& in_array( $notification->getType(), $supportedNotificationTypes, true )
			&& $notification->getAgent()->equals( $this->getMessengerUser() )
		) {
			return FilterMiddlewareAction::REMOVE;
		}
		return FilterMiddlewareAction::KEEP;
	}

}

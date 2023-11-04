<?php

namespace MediaWiki\MassMessage;

use EchoEvent;
use MediaWiki\Extension\Notifications\Hooks\BeforeEchoEventInsertHook;

/**
 * All hooks from the Echo extension which is optional to use with this extension.
 */
class EchoHooks implements
	BeforeEchoEventInsertHook
{
	/**
	 * @param EchoEvent $event
	 * @return bool
	 */
	public function onBeforeEchoEventInsert( EchoEvent $event ): bool {
		// Don't spam a user with mention notifications if it's a MassMessage
		if (
			( $event->getType() === 'mention' || $event->getType() === 'flow-mention' ) &&
			// getAgent() can return null, so guard against that
			$event->getAgent() &&
			$event->getAgent()->getId() == MassMessage::getMessengerUser()->getId()
		) {
			return false;
		}
		return true;
	}
}

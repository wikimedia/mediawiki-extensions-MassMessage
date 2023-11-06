<?php

namespace MediaWiki\Extension\Notifications\Hooks;

use MediaWiki\Extension\Notifications\Model\Event;

/**
 * Stub of Echo's BeforeEchoEventInsertHook interface for phan
 */
interface BeforeEchoEventInsertHook {
	/**
	 * @param Event $event
	 * @return bool|void True or no return value to continue or false to abort
	 */
	public function onBeforeEchoEventInsert( Event $event );
}

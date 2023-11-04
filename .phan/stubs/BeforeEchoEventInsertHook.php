<?php

namespace MediaWiki\Extension\Notifications\Hooks;

use EchoEvent;

/**
 * Stub of Echo's BeforeEchoEventInsertHook interface for phan
 */
interface BeforeEchoEventInsertHook {
	/**
	 * @param EchoEvent $event
	 * @return bool|void True or no return value to continue or false to abort
	 */
	public function onBeforeEchoEventInsert( EchoEvent $event );
}

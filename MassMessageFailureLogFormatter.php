<?php

/**
 * Log formatter for 'failure' entries on Special:Log/massmessage
 * This lets us use <code></code> tags in the message
 */

class MassMessageFailureLogFormatter extends LogFormatter {

	/**
	 * getActionMessage() typically returns a Message object, but
	 * if we return a string, it will just take that raw string and use it
	 *
	 * @return string
	 */
	public function getActionMessage() {
		$msg = parent::getActionMessage();
		return $msg->parse();
	}
}
<?php

/**
* Translations and stuff.
*
* @file
* @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
*/

$messages = array();


/** English
 * @author Kunal Mehta
 */

$messages['en'] = array(
	'massmessage' => 'Send mass message',
	'massmessage-desc' => 'Allows users to easily send a message to a list of users',
	'massmessage-sender' => 'MessengerBot',
	'massmessage-form-spamlist' => 'Page containing list of pages to leave a message on.',
	'massmessage-form-subject' => 'Subject of the message. Will also be used as the edit summary.',
	'massmessage-form-message' => 'The body of the message.',
	'massmessage-form-global' => 'This is a global message.',
	'massmessage-form-submit' => 'Send',
	'massmessage-submitted' => 'Your message has been sent!',
	'massmessage-account-blocked' => 'The account used to deliver messages has been blocked.',
	'massmessage-spamlist-doesnotexist' => 'The input list of pages does not exist.',
	'right-massmessage' => 'Send a message to multiple users at once',
	'action-massmessage' => 'send a message to multiple users at once',
	'right-massmessage-global' => 'Send a message to multiple users on different wikis at once',
	'log-name-massmessage' => 'Mass message log',
	'log-description-massmessage' => 'These events track users sending messages through [[Special:MassMessage]].',
	'logentry-massmessage-send' => '$1 {{GENDER:$2|sent a message}} to $3'
);

/** Message documentation
 * @author Kunal Mehta
 */
$messages['qqq'] = array(
	'massmessage' => '{{doc-special|MassMessage}}',
	'massmessage-desc' => '{{desc|name=MassMessage|url=https://www.mediawiki.org/wiki/Extension:MassMessage}}',
	'massmessage-sender' => 'Username of the account which sends out messages.',
	'massmessage-form-spamlist' => 'Label for an inputbox on the special page.',
	'massmessage-form-subject' => 'Label for an inputbox on the special page.',
	'massmessage-form-message' => 'Label for an inputbox on the special page.',
	'massmessage-form-global' => 'Label for a checkbox on the special page.',
	'massmessage-form-submit' => 'Label for the submit button on the special page.',
	'massmessage-submitted' => 'Confirmation message the user sees after the form is submitted successfully.',
	'massmessage-account-blocked' => 'Error message the user sees if the bot account has been blocked.',
	'massmessage-spamlist-doesnotexist' => 'Error message the user sees if an invalid spamlist is provided.',
	'right-massmessage' => '{{doc-right|massmessage}}',
	'action-massmessage' => '{{doc-action|massmessage}}',
	'right-massmessage-global' => '{{doc-right|massmessage-global}}',
	'log-name-massmessage' => 'Log page title',
	'log-description-massmessage' => 'Log page description',
	'logentry-massmessage-send' => '{{logentry}}'
);

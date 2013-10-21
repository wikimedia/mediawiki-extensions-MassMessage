<?php
/*
 * Easily send a message to multiple users at once.
 * Based on code from TranslationNotifications
 * https://mediawiki.org/wiki/Extension:TranslationNotifications
 *
 * @file
 * @ingroup Extensions
 * @author Kunal Mehta
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

// Protect against web entry
if ( !defined( 'MEDIAWIKI' ) ) {
	exit;
}

/*
 * Namespaces to post in
 *
 * Only let the bot post in these namespaces regardless
 * of what the user specificed in the input list. This is checked
 * after $wgNamespacesToConvert is applied.
 * Applies to both local and global messages.
 */
$wgNamespacesToPostIn = array( NS_PROJECT, NS_USER_TALK );

/*
 * Namespaces to convert
 *
 * If you want users to be able to provide a link to a User: page,
 * but have the bot post on their User talk: page you can define that here.
 * Applies to both local and global messages.
 */
$wgNamespacesToConvert = array( NS_USER => NS_USER_TALK );

/*
 * Username of the messenger bot
 *
 * This ensures that local administrators cannot change the bot's username
 * by editing a system message, which would interfere with global messages
 */
$wgMassMessageAccountUsername = 'MessengerBot';

/**
 * Whether to allow sending messages to another wiki
 *
 * This can be enabled on a "central" wiki to make it
 * easier to keep track of where messages are being sent
 * from.
 */
$wgAllowGlobalMessaging = true;

$wgExtensionCredits['specialpage'][] = array(
	'path' => __FILE__,
	'name' => 'MassMessage',
	'author' => 'Kunal Mehta',
	'url' => 'https://www.mediawiki.org/wiki/Extension:MassMessage',
	'descriptionmsg' => 'massmessage-desc',
	'version' => '0.0.1',
);
$dir = dirname( __FILE__ );

$wgSpecialPages['MassMessage'] = 'SpecialMassMessage';
$wgExtensionMessagesFiles['MassMessage'] = "$dir/MassMessage.i18n.php";
$wgExtensionMessagesFiles['MassMessageAlias'] = "$dir/MassMessage.alias.php";
$wgExtensionMessagesFiles['MassMessageMagic'] = "$dir/MassMessage.i18n.magic.php";
$wgAutoloadClasses['MassMessage'] = "$dir/MassMessage.body.php";
$wgAutoloadClasses['MassMessageHooks'] = "$dir/MassMessage.hooks.php";
$wgAutoloadClasses['SpecialMassMessage'] = "$dir/SpecialMassMessage.php";
$wgAutoloadClasses['MassMessageJob'] = "$dir/MassMessageJob.php";
$wgAutoloadClasses['MassMessageSubmitJob'] = "$dir/MassMessageSubmitJob.php";
$wgAutoloadClasses['MassMessageFailureLogFormatter'] = "$dir/MassMessageFailureLogFormatter.php";
$wgAutoloadClasses['MassMessageSendLogFormatter'] = "$dir/MassMessageSendLogFormatter.php";
$wgJobClasses['MassMessageJob'] = 'MassMessageJob';
$wgJobClasses['MassMessageSubmitJob'] = 'MassMessageSubmitJob';

$wgHooks['ParserFirstCallInit'][] = 'MassMessageHooks::onParserFirstCallInit';
$wgHooks['SpecialStatsAddExtra'][] = 'MassMessageHooks::onSpecialStatsAddExtra';
$wgHooks['APIQuerySiteInfoStatisticsInfo'][] = 'MassMessageHooks::onAPIQuerySiteInfoStatisticsInfo';
$wgHooks['RenameUserPreRename'][] = 'MassMessageHooks::onRenameUserPreRename';
$wgHooks['UserGetReservedNames'][] = 'MassMessageHooks::onUserGetReservedNames';
$wgHooks['UnitTestsList'][] = 'MassMessageHooks::onUnitTestsList';
$wgHooks['BeforeEchoEventInsert'][] = 'MassMessageHooks::onBeforeEchoEventInsert';

$wgResourceModules['ext.MassMessage.special.js'] = array(
	'scripts' => array(
		'ext.MassMessage.special.js',
		'ext.MassMessage.autocomplete.js',
		'ext.MassMessage.badhtml.js',
	),
	'messages' => array( 'massmessage-badhtml' ),
	'dependencies' => array(
		'jquery.byteLimit',
		'jquery.ui.autocomplete',
		'jquery.delayedBind',
		'mediawiki.jqueryMsg',
	),
	'localBasePath' => $dir . '/modules',
	'remoteExtPath' => 'MassMessage/modules',
);

$wgResourceModules['ext.MassMessage.special'] = array(
	'styles' => 'ext.MassMessage.special.css',
	'localBasePath' => $dir . '/modules',
	'remoteExtPath' => 'MassMessage/modules',
);

$wgLogTypes[] = 'massmessage';
$wgLogActionsHandlers['massmessage/*'] = 'LogFormatter';
$wgLogActionsHandlers['massmessage/send'] = 'MassMessageSendLogFormatter';
$wgLogActionsHandlers['massmessage/failure'] = 'MassMessageFailureLogFormatter';

// User rights
$wgAvailableRights[] = 'massmessage'; // Local messaging
$wgGroupPermissions['sysop']['massmessage'] = true;

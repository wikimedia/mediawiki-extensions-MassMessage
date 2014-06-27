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
 * Only let the bot post in these namespaces plus all talk namespaces regardless
 * of what the user specificed in the input list. This is checked after $wgNamespacesToConvert
 * is applied. Applies to both local and global messages.
 */
$wgNamespacesToPostIn = array( NS_PROJECT );

/*
 * Namespaces to convert
 *
 * If you want users to be able to provide a link to a User: page, but have the bot
 * post on their User talk: page you can define that here. Applies to both local
 * and global messages.
 */
$wgNamespacesToConvert = array( NS_USER => NS_USER_TALK );

/*
 * Username of the messenger bot
 *
 * This ensures that local administrators cannot change the bot's username by editing
 * a system message, which would interfere with global messages.
 */
$wgMassMessageAccountUsername = 'MediaWiki message delivery';

/**
 * Whether to allow sending messages to another wiki
 *
 * This can be enabled on a "central" wiki to make it easier to keep track of where
 * messages are being sent from. Changing this from true to false will render existing
 * delivery lists containing external targets invalid.
 */
$wgAllowGlobalMessaging = true;

$wgExtensionCredits['specialpage'][] = array(
	'path' => __FILE__,
	'name' => 'MassMessage',
	'author' => array( 'Kunal Mehta', 'wctaiwan' ),
	'url' => 'https://www.mediawiki.org/wiki/Extension:MassMessage',
	'descriptionmsg' => 'massmessage-desc',
	'version' => '0.2.0',
);
$dir = __DIR__;

// Messages
$wgMessagesDirs['MassMessage'] = "$dir/i18n";
$wgExtensionMessagesFiles['MassMessageAlias'] = "$dir/MassMessage.alias.php";
$wgExtensionMessagesFiles['MassMessageMagic'] = "$dir/MassMessage.i18n.magic.php";

// Classes
$wgAutoloadClasses['MassMessageHooks'] = "$dir/MassMessage.hooks.php";
$wgAutoloadClasses['ApiMassMessage'] = "$dir/includes/ApiMassMessage.php";
$wgAutoloadClasses['MassMessage'] = "$dir/includes/MassMessage.php";
$wgAutoloadClasses['SpecialMassMessage'] = "$dir/includes/SpecialMassMessage.php";
$wgAutoloadClasses['SpecialCreateMassMessageList'] = "$dir/includes/SpecialCreateMassMessageList.php";
$wgAutoloadClasses['SpecialEditMassMessageList'] = "$dir/includes/SpecialEditMassMessageList.php";
$wgAutoloadClasses['MassMessageJob'] = "$dir/includes/job/MassMessageJob.php";
$wgAutoloadClasses['MassMessageSubmitJob'] = "$dir/includes/job/MassMessageSubmitJob.php";
$wgAutoloadClasses['MassMessageFailureLogFormatter'] = "$dir/includes/logging/MassMessageFailureLogFormatter.php";
$wgAutoloadClasses['MassMessageSendLogFormatter'] = "$dir/includes/logging/MassMessageSendLogFormatter.php";
$wgAutoloadClasses['MassMessageSkipLogFormatter'] = "$dir/includes/logging/MassMessageSkipLogFormatter.php";
$wgAutoloadClasses['MassMessageListContent'] = "$dir/includes/content/MassMessageListContent.php";
$wgAutoloadClasses['MassMessageListContentHandler'] = "$dir/includes/content/MassMessageListContentHandler.php";

// ContentHandler
$wgContentHandlers['MassMessageListContent'] = 'MassMessageListContentHandler';

// API modules
$wgAPIModules['massmessage'] = 'ApiMassMessage';

// Job classes
$wgJobClasses['MassMessageJob'] = 'MassMessageJob';
$wgJobClasses['MassMessageSubmitJob'] = 'MassMessageSubmitJob';

// Hooks
$wgHooks['ParserFirstCallInit'][] = 'MassMessageHooks::onParserFirstCallInit';
$wgHooks['SpecialStatsAddExtra'][] = 'MassMessageHooks::onSpecialStatsAddExtra';
$wgHooks['APIQuerySiteInfoStatisticsInfo'][] = 'MassMessageHooks::onAPIQuerySiteInfoStatisticsInfo';
$wgHooks['RenameUserPreRename'][] = 'MassMessageHooks::onRenameUserPreRename';
$wgHooks['UserGetReservedNames'][] = 'MassMessageHooks::onUserGetReservedNames';
$wgHooks['UnitTestsList'][] = 'MassMessageHooks::onUnitTestsList';
$wgHooks['BeforeEchoEventInsert'][] = 'MassMessageHooks::onBeforeEchoEventInsert';
$wgHooks['SkinTemplateNavigation'][] = 'MassMessageHooks::onSkinTemplateNavigation';

// Special pages
$wgSpecialPages['MassMessage'] = 'SpecialMassMessage';
$wgSpecialPages['CreateMassMessageList'] = 'SpecialCreateMassMessageList';
$wgSpecialPages['EditMassMessageList'] = 'SpecialEditMassMessageList';

// ResourceLoader
$wgResourceModules['ext.MassMessage.special.js'] = array(
	'scripts' => array(
		'ext.MassMessage.special.js',
		'ext.MassMessage.autocomplete.js',
		'ext.MassMessage.badhtml.js',
	),
	'messages' => array( 'massmessage-badhtml', 'massmessage-parse-badpage' ),
	'dependencies' => array(
		'jquery.byteLimit',
		'jquery.ui.autocomplete',
		'jquery.throttle-debounce',
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
$wgResourceModules['ext.MassMessage.create'] = array(
	'scripts' => array(
		'ext.MassMessage.create.js',
	),
	'localBasePath' => $dir . '/modules',
	'remoteExtPath' => 'MassMessage/modules',
);

// Logging
$wgLogTypes[] = 'massmessage';
$wgLogActionsHandlers['massmessage/*'] = 'LogFormatter';
$wgLogActionsHandlers['massmessage/send'] = 'MassMessageSendLogFormatter';
$wgLogActionsHandlers['massmessage/failure'] = 'MassMessageFailureLogFormatter';
$wgLogActionsHandlers['massmessage/skipoptout'] = 'MassMessageSkipLogFormatter';
$wgLogActionsHandlers['massmessage/skipnouser'] = 'MassMessageSkipLogFormatter';
$wgLogActionsHandlers['massmessage/skipbadns'] = 'MassMessageSkipLogFormatter';

// User rights
$wgAvailableRights[] = 'massmessage'; // Local messaging
$wgGroupPermissions['sysop']['massmessage'] = true;

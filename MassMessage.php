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
 * messages are being sent from.
 */
$wgAllowGlobalMessaging = true;

$wgExtensionCredits['specialpage'][] = array(
	'path' => __FILE__,
	'name' => 'MassMessage',
	'author' => array( 'Kunal Mehta', 'wctaiwan' ),
	'url' => 'https://www.mediawiki.org/wiki/Extension:MassMessage',
	'descriptionmsg' => 'massmessage-desc',
	'version' => '0.4.0',
	'license-name' => 'GPL-2.0+',
);

// Messages
$wgMessagesDirs['MassMessage'] = __DIR__ . '/i18n';
$wgExtensionMessagesFiles['MassMessageAlias'] = __DIR__ . '/MassMessage.alias.php';
$wgExtensionMessagesFiles['MassMessageMagic'] = __DIR__ . '/MassMessage.i18n.magic.php';

// Classes
$wgAutoloadClasses['MassMessageHooks'] = __DIR__ . '/MassMessage.hooks.php';
$wgAutoloadClasses['ApiMassMessage'] = __DIR__ . '/includes/ApiMassMessage.php';
$wgAutoloadClasses['ApiEditMassMessageList'] = __DIR__ . '/includes/ApiEditMassMessageList.php';
$wgAutoloadClasses['ApiQueryMMSites'] = __DIR__ . '/includes/ApiQueryMMSites.php';
$wgAutoloadClasses['MassMessage'] = __DIR__ . '/includes/MassMessage.php';
$wgAutoloadClasses['MassMessageTargets'] = __DIR__ . '/includes/MassMessageTargets.php';
$wgAutoloadClasses['SpecialMassMessage'] = __DIR__ . '/includes/SpecialMassMessage.php';
$wgAutoloadClasses['SpecialCreateMassMessageList']
	= __DIR__ . '/includes/SpecialCreateMassMessageList.php';
$wgAutoloadClasses['SpecialEditMassMessageList']
	= __DIR__ . '/includes/SpecialEditMassMessageList.php';
$wgAutoloadClasses['MassMessageJob'] = __DIR__ . '/includes/job/MassMessageJob.php';
$wgAutoloadClasses['MassMessageServerSideJob']
	= __DIR__ . '/includes/job/MassMessageServerSideJob.php';
$wgAutoloadClasses['MassMessageSubmitJob'] = __DIR__ . '/includes/job/MassMessageSubmitJob.php';
$wgAutoloadClasses['MassMessageFailureLogFormatter']
	= __DIR__ . '/includes/logging/MassMessageFailureLogFormatter.php';
$wgAutoloadClasses['MassMessageSendLogFormatter']
	= __DIR__ . '/includes/logging/MassMessageSendLogFormatter.php';
$wgAutoloadClasses['MassMessageSkipLogFormatter']
	= __DIR__ . '/includes/logging/MassMessageSkipLogFormatter.php';
$wgAutoloadClasses['MassMessageListContent']
	= __DIR__ . '/includes/content/MassMessageListContent.php';
$wgAutoloadClasses['MassMessageListContentHandler']
	= __DIR__ . '/includes/content/MassMessageListContentHandler.php';
$wgAutoloadClasses['MassMessageListDiffEngine']
	= __DIR__ . '/includes/content/MassMessageListDiffEngine.php';
$wgAutoloadClasses['MassMessageTestCase'] = __DIR__ . '/tests/MassMessageTestCase.php';
$wgAutoloadClasses['MassMessageApiTestCase'] = __DIR__ . '/tests/MassMessageApiTestCase.php';

// ContentHandler
$wgContentHandlers['MassMessageListContent'] = 'MassMessageListContentHandler';

// API modules
$wgAPIModules['massmessage'] = 'ApiMassMessage';
$wgAPIModules['editmassmessagelist'] = 'ApiEditMassMessageList';
$wgAPIListModules['mmsites'] = 'ApiQueryMMSites';

// Job classes
$wgJobClasses['MassMessageJob'] = 'MassMessageJob';
$wgJobClasses['MassMessageSubmitJob'] = 'MassMessageSubmitJob';
$wgJobClasses['MassMessageServerSideJob'] = 'MassMessageServerSideJob';

// Hooks
$wgHooks['ParserFirstCallInit'][] = 'MassMessageHooks::onParserFirstCallInit';
$wgHooks['SpecialStatsAddExtra'][] = 'MassMessageHooks::onSpecialStatsAddExtra';
$wgHooks['APIQuerySiteInfoStatisticsInfo'][] = 'MassMessageHooks::onAPIQuerySiteInfoStatisticsInfo';
$wgHooks['RenameUserPreRename'][] = 'MassMessageHooks::onRenameUserPreRename';
$wgHooks['UserGetReservedNames'][] = 'MassMessageHooks::onUserGetReservedNames';
$wgHooks['UnitTestsList'][] = 'MassMessageHooks::onUnitTestsList';
$wgHooks['BeforeEchoEventInsert'][] = 'MassMessageHooks::onBeforeEchoEventInsert';
$wgHooks['SkinTemplateNavigation'][] = 'MassMessageHooks::onSkinTemplateNavigation';
$wgHooks['BeforePageDisplay'][] = 'MassMessageHooks::onBeforePageDisplay';

// Special pages
$wgSpecialPages['MassMessage'] = 'SpecialMassMessage';
$wgSpecialPages['CreateMassMessageList'] = 'SpecialCreateMassMessageList';
$wgSpecialPages['EditMassMessageList'] = 'SpecialEditMassMessageList';

// ResourceLoader
$wgResourceModules['ext.MassMessage.autocomplete'] = array(
	'scripts' => 'ext.MassMessage.autocomplete.js',
	'dependencies' => 'jquery.ui.autocomplete',
	'localBasePath' => __DIR__ . '/modules',
	'remoteExtPath' => 'MassMessage/modules',
);
$wgResourceModules['ext.MassMessage.special.js'] = array(
	'scripts' => array(
		'ext.MassMessage.special.js',
		'ext.MassMessage.badhtml.js',
	),
	'styles' => 'ext.MassMessage.validation.css',
	'messages' => array(
		'massmessage-badhtml',
		'massmessage-parse-badpage'
	),
	'dependencies' => array(
		'ext.MassMessage.autocomplete',
		'jquery.byteLimit',
		'jquery.throttle-debounce',
		'mediawiki.jqueryMsg',
	),
	'localBasePath' => __DIR__ . '/modules',
	'remoteExtPath' => 'MassMessage/modules',
);
$wgResourceModules['ext.MassMessage.special'] = array(
	'styles' => 'ext.MassMessage.special.css',
	'localBasePath' => __DIR__ . '/modules',
	'remoteExtPath' => 'MassMessage/modules',
);
$wgResourceModules['ext.MassMessage.content'] = array(
	'styles' => 'ext.MassMessage.content.css',
	'localBasePath' => __DIR__ . '/modules',
	'remoteExtPath' => 'MassMessage/modules',
);
$wgResourceModules['ext.MassMessage.content.js'] = array(
	'scripts' => 'ext.MassMessage.content.js',
	'messages' => array(
		'massmessage-content-remove',
		'massmessage-content-emptylist',
		'massmessage-content-addeditem',
		'massmessage-content-removeerror',
		'massmessage-content-removeconf',
		'massmessage-content-removeyes',
		'massmessage-content-removeno',
		'massmessage-content-alreadyinlist',
		'massmessage-content-invalidtitlesite',
		'massmessage-content-invalidtitle',
		'massmessage-content-invalidsite',
		'massmessage-content-adderror',
	),
	'dependencies' => array(
		'ext.MassMessage.autocomplete',
		'jquery.confirmable',
		'mediawiki.api',
		'mediawiki.util',
		'mediawiki.jqueryMsg',
	),
	'localBasePath' => __DIR__ . '/modules',
	'remoteExtPath' => 'MassMessage/modules',
);
$wgResourceModules['ext.MassMessage.content.noedit'] = array(
	'styles' => 'ext.MassMessage.content.noedit.css',
	'localBasePath' => __DIR__ . '/modules',
	'remoteExtPath' => 'MassMessage/modules',
);
$wgResourceModules['ext.MassMessage.content.nojs'] = array(
	'styles' => 'ext.MassMessage.content.nojs.css',
	'localBasePath' => __DIR__ . '/modules',
	'remoteExtPath' => 'MassMessage/modules',
);
$wgResourceModules['ext.MassMessage.create'] = array(
	'scripts' => 'ext.MassMessage.create.js',
	'styles' => 'ext.MassMessage.validation.css',
	'messages' => array(
		'massmessage-create-exists-short',
		'massmessage-create-invalidsource-short',
	),
	'dependencies' => array(
		'mediawiki.jqueryMsg',
		'ext.MassMessage.autocomplete',
	),
	'localBasePath' => __DIR__ . '/modules',
	'remoteExtPath' => 'MassMessage/modules',
);
$wgResourceModules['ext.MassMessage.edit'] = array(
	'scripts' => 'ext.MassMessage.edit.js',
	'dependencies' => 'jquery.byteLimit',
	'localBasePath' => __DIR__ . '/modules',
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

// Tracking category for spamlists
$wgTrackingCategories[] = 'massmessage-list-category';

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
$wgJobClasses['massmessageJob'] = 'MassMessageJob';

$wgHooks['ParserFirstCallInit'][] = 'MassMessageHooks::onParserFirstCallInit';
$wgHooks['SpecialStatsAddExtra'][] = 'MassMessageHooks::onSpecialStatsAddExtra';
$wgHooks['UnitTestsList'][] = 'MassMessageHooks::onUnitTestsList';

$wgResourceModules['ext.MassMessage.special'] = array(
	'scripts' => 'ext.MassMessage.special.js',
	'dependencies' => array(
		'jquery.byteLimit',
	),

	'localBasePath' => $dir,
);

$wgLogTypes[] = 'massmessage';
$wgLogActionsHandlers['massmessage/*'] = 'LogFormatter';
$wgAvailableRights[] = 'massmessage'; // Local messaging
$wgAvailableRights[] = 'massmessage-global'; // Cross-wiki messaging
$wgGroupPermissions['sysop']['massmessage'] = true;

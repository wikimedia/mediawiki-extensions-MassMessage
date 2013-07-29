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
 * Namespaces to extract links for
 *
 * From your spamlist, only links to these
 * domains will be checked.
 * Only applies to local messages.
 */
$wgNamespacesToExtractLinksFor = array( NS_PROJECT, NS_USER, NS_USER_TALK );

/*
 * Namespaces to convert
 *
 * If you want users to be able to provide a link to a User: page,
 * but have the bot post on their User talk: page you can define that here.
 * Only applies to local messages.
 */
$wgNamespacesToConvert = array( NS_USER => NS_USER_TALK );

/*
 * Remote account's password
 *
 * Only required for global messages.
 */
$wgMassMessageAccountPassword = '';

 
$wgExtensionCredits[ 'specialpage' ][] = array(
	'path' => __FILE__,
	'name' => 'MassMessage',
	'author' => 'Kunal Mehta',
	'url' => 'https://www.mediawiki.org/wiki/Extension:MassMessage',
	'descriptionmsg' => 'massmessage-desc',
	'version' => '0.0.1',
);
 $dir = dirname(__FILE__);

$wgSpecialPages[ 'MassMessage' ] = 'SpecialMassMessage';
$wgExtensionMessagesFiles['MassMessage'] = "$dir/MassMessage.i18n.php";
$wgExtensionMessagesFiles['MassMessageAlias'] = "$dir/MassMessage.alias.php";
$wgAutoloadClasses['MassMessage'] = "$dir/MassMessage.body.php";
$wgAutoloadClasses['SpecialMassMessage'] = "$dir/SpecialMassMessage.php";
$wgAutoloadClasses['MassMessageJob'] = "$dir/MassMessageJob.php";
$wgJobClasses['massmessageJob'] = 'MassMessageJob';

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
$wgGroupPermissions['messenger']['massmessage'] = true;
$wgGroupPermissions['sysop']['massmessage'] = true;

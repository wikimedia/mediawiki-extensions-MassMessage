<?php

if ( function_exists( 'wfLoadExtension' ) ) {
	wfLoadExtension( 'MassMessage' );
	// Keep i18n globals so mergeMessageFileList.php doesn't break
	$wgMessagesDirs['MassMessage'] = __DIR__ . '/i18n';
	$wgExtensionMessagesFiles['MassMessageAlias'] = __DIR__ . '/MassMessage.alias.php';
	$wgExtensionMessagesFiles['MassMessageMagic'] = __DIR__ . '/MassMessage.i18n.magic.php';
	wfWarn(
		'Deprecated PHP entry point used for MassMessage extension. Please use wfLoadExtension instead,' .
		' see https://www.mediawiki.org/wiki/Extension_registration for more details.'
	);
	return true;
} else {
	die( 'This version of the MassMessage extension requires MediaWiki 1.31+' );
}

<?php

$cfg = require __DIR__ . '/../../vendor/mediawiki/mediawiki-phan-config/src/config.php';

$cfg['suppress_issue_types'] = array_merge( $cfg['suppress_issue_types'], [
	// IContextSource::msg() takes multiple parameters
	'PhanParamTooMany',
	// \LogFormatter->parsedParameters Error depends on core
	'PhanUndeclaredProperty',
	// False-positive
	'PhanTypeMismatchForeach',
	// getDescription, getTargets, getValidTargets declared in MassMessageListContent
	'PhanUndeclaredMethod',
	// False-positive
	'PhanUndeclaredClassInCallable',
	// False-positive
	'PhanUndeclaredStaticMethod',
] );

return $cfg;

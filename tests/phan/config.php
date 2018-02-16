<?php

$cfg = require __DIR__ . '/../../vendor/mediawiki/mediawiki-phan-config/src/config.php';

$cfg['suppress_issue_types'] = [
	// Linker::link(), wfMemcKey(), wfGlobalCacheKey() are false-positive
	'PhanDeprecatedFunction',
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
];

return $cfg;

<?php

$cfg = require __DIR__ . '/../vendor/mediawiki/mediawiki-phan-config/src/config.php';

// getDescription, getTargets, getValidTargets declared in MassMessageListContent
$cfg['suppress_issue_types'][] = 'PhanUndeclaredMethod';

return $cfg;

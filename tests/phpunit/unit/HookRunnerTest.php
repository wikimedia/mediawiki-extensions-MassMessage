<?php

namespace MediaWiki\MassMessage\Test\Unit;

use MediaWiki\MassMessage\Job\Hooks\HookRunner;
use MediaWiki\Tests\HookContainer\HookRunnerTestBase;

/**
 * @covers \MediaWiki\MassMessage\Job\Hooks\HookRunner
 */
class HookRunnerTest extends HookRunnerTestBase {

	public static function provideHookRunners() {
		yield HookRunner::class => [ HookRunner::class ];
	}
}

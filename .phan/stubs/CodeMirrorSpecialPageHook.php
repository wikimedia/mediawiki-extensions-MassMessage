<?php

namespace MediaWiki\Extension\CodeMirror\Hooks;

use MediaWiki\SpecialPage\SpecialPage;

/**
 * Stub of CodeMirror's CodeMirrorSpecialPageHook interface for phan
 */
interface CodeMirrorSpecialPageHook {
	/**
	 * @param SpecialPage $special
	 * @param array &$textareas
	 * @return bool True to continue or false to abort
	 */
	public function onCodeMirrorSpecialPage( SpecialPage $special, array &$textareas ): bool;
}

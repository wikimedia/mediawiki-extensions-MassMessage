<?php

namespace MediaWiki\MassMessage;

use MediaWiki\Extension\CodeMirror\Hooks\CodeMirrorSpecialPageHook;
use MediaWiki\SpecialPage\SpecialPage;

/**
 * All hooks from the CodeMirror extension which is optional to use with this extension.
 */
class CodeMirrorHooks implements CodeMirrorSpecialPageHook {
	/**
	 * @param SpecialPage $special
	 * @param array &$textareas
	 * @return bool
	 */
	public function onCodeMirrorSpecialPage( SpecialPage $special, array &$textareas ): bool {
		if ( $special->getName() === 'MassMessage' ) {
			$textareas[] = '#mw-massmessage-form-message textarea';
			return false;
		}
		return true;
	}
}

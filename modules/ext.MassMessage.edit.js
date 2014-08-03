( function ( mw, $ ) {
	$( function () {
		'use strict';

		// Limit edit summaries to 240 bytes
		// From ext.MassMessage.special.js
		$( '#mw-input-wpsummary' ).byteLimit();
	} );
}( mediaWiki, jQuery ) );

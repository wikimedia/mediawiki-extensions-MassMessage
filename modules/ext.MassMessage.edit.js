$( function () {
	'use strict';

	// Limit edit summaries to 240 bytes
	// From ext.MassMessage.special.js
	var $summary = $( '#mw-input-wpsummary' );

	if ( $summary.length ) {
		mw.widgets.visibleByteLimit( OO.ui.infuse( $summary ) );
	}
} );

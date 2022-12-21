$( function () {
	'use strict';

	// Limit edit summaries to 240 bytes
	// From ext.MassMessage.special.js
	var $summary = $( '#mw-input-wpsummary' );

	if ( $summary.length ) {
		mw.widgets.visibleByteLimit( OO.ui.infuse( $summary ) );
	}

	// Uses the same method as ext.abuseFilter.edit.js from the AbuseFilter extension.
	var warnOnLeave,
		$form = $( '#mw-massmessage-edit-form' ),
		origValues = $form.serialize();

	warnOnLeave = mw.confirmCloseWindow( {
		test: function () {
			return $form.serialize() !== origValues;
		}
	} );

	$form.on( 'submit', function () {
		warnOnLeave.release();
	} );
} );

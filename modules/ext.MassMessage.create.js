( function ( mw, $ ) {
	$( function () {
		'use strict';

		// Set the correct field state on load.
		if ( $( '#mw-input-wpcontent-import' ).is( ':checked' ) ) {
			$( '#mw-input-wpsource' ).prop( 'disabled', false );
		} else {
			$( '#mw-input-wpsource' ).prop( 'disabled', true );
		}

		$( '#mw-input-wpcontent-new' ).click( function () {
			$( '#mw-input-wpsource' ).prop( 'disabled', true );
		} );

		$( '#mw-input-wpcontent-import' ).click( function () {
			$( '#mw-input-wpsource' ).prop( 'disabled', false );
		} );

	} );
}( mediaWiki, jQuery ) );

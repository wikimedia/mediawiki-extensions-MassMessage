( function ( mw, $ ) {
	$( function () {
		/*global alert*/
		'use strict';

		$( '#mw-content-text' ).on( 'click', '.mw-massmessage-removelink a', function ( e ) {
			var param, $link = $( this );

			e.preventDefault();
			if ( $link.attr( 'data-site' ) === 'local' ) {
				param = $link.attr( 'data-title' );
			} else {
				param = $link.attr( 'data-title' ) + '@' + $link.attr( 'data-site' );
			}

			( new mw.Api() ).postWithToken( 'edit', {
				action: 'editmassmessagelist',
				spamlist: mw.config.get( 'wgPageName' ),
				remove: param
			} )
			.done( function () {
				$link.closest( 'li' ).fadeOut();
			} )
			.fail( function ( errorCode ) {
				// TODO: Use something other than alert()?
				alert( mw.msg( 'massmessage-content-removeerror', errorCode ) );
			} );
		} );
	} );
}( mediaWiki, jQuery ) );

/**
 * Add autocomplete suggestions to the spamlist input.
 * Mainly from from http://jqueryui.com/autocomplete/
 * and resources/mediawiki/mediawiki.searchSuggest.js
 */
( function ( mw, $ ) {
	$( function () {
		'use strict';
		$( '#mw-massmessage-form-spamlist' ).autocomplete({
			source: function( request, response ) {
				var api = new mw.Api();
				api.get({
					action: 'opensearch',
					search: request.term,
					suggest: ''
				}).done( function (data) {
						response( data[1] );
					});
			},
			select: function (event, ui ) {
				$( '#mw-massmessage-form-spamlist' ).val( ui.term );
			}
		});
	} );

}( mediaWiki, jQuery ) );

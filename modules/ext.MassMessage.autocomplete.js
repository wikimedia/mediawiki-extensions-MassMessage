/**
 * Functions for autocomplete of titles and sites
 * Mainly from from http://jqueryui.com/autocomplete/
 * and resources/mediawiki/mediawiki.searchSuggest.js
 */
( function () {
	'use strict';
	mw.massmessage = mw.massmessage || {};

	mw.massmessage.enableTitleComplete = function ( $selector ) {
		$selector.autocomplete( {
			source: function ( request, response ) {
				var api = new mw.Api();
				api.get( {
					action: 'opensearch',
					search: request.term,
					suggest: ''
				} ).done( function ( data ) {
					response( data[ 1 ] );
				} );
			}
		} );
	};

	mw.massmessage.enableSiteComplete = function ( $selector ) {
		$selector.autocomplete( {
			source: function ( request, response ) {
				( new mw.Api() ).get( {
					action: 'query',
					list: 'mmsites',
					term: request.term
				} ).done( function ( data ) {
					response( data.query.mmsites );
				} );
			}
		} );
	};
}() );

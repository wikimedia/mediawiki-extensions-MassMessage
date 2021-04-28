/**
 * Functions for autocomplete of titles and sites
 * Mainly from from http://jqueryui.com/autocomplete/
 * and resources/mediawiki/mediawiki.searchSuggest.js
 *
 * Warning: This file may be executed multiple times in the same request, since it is
 * used by multiple modules.
 *
 * TODO convert to packageFiles to avoid multiple executions
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
					search: request.term
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

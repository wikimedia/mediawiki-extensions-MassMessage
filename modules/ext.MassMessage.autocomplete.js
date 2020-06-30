/**
 * Functions for autocomplete of titles and sites
 * Mainly from from http://jqueryui.com/autocomplete/
 * and resources/mediawiki/mediawiki.searchSuggest.js
 */
'use strict';

/**
 * @param {jQuery} $selector
 */
function enableTitleComplete( $selector ) {
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
}

/**
 * @param {jQuery} $selector
 */
function enableSiteComplete( $selector ) {
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
}

module.exports = {
	enableTitleComplete: enableTitleComplete,
	enableSiteComplete: enableSiteComplete
};

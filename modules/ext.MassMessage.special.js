$( function () {
	'use strict';

	// Dynamic page title validation
	var autocomplete = require( './ext.MassMessage.autocomplete.js' ),
		badHtml = require( './ext.MassMessage.badhtml.js' ),
		$spamlist = $( '#mw-massmessage-form-spamlist' ),
		$massmessagepage = $( '#mw-massmessage-form-page' );

	// Limit edit summaries to 240 bytes
	$( '#mw-massmessage-form-subject' ).byteLimit();

	badHtml( $( '#mw-massmessage-form-message' ) );

	/**
	 * Fetch pages with a given title.
	 *
	 * @param {string} pagetitle
	 * @return {Promise}
	 */
	function getPagesByTitle( pagetitle ) {
		var api = new mw.Api();

		return api.get( {
			action: 'query',
			titles: pagetitle,
			prop: 'info',
			formatversion: 2
		} ).done( function ( data ) {
			return data;
		} );
	}

	/**
	 * Adds a status field for the element.
	 *
	 * @param {Object} $elem jQuery element for which the status field has to be added.
	 * @return {Object} jQuery element
	 */
	function addStatusField( $elem ) {
		var $statusField = $( '<span>' )
			.prop( 'id', $elem.prop( 'id' ) + '-status' )
			.insertAfter( $elem );

		return $statusField;
	}

	/**
	 * Adds page title validation for a given text field
	 *
	 * @param {Object} $elem jQuery element to
	 * @param {Function} callback Called when we recieve some pages in response.
	 */
	function addPageTitleValidation( $elem, callback ) {
		var $statusField = addStatusField( $elem );
		$elem.on( 'input autocompletechange', $.debounce( 250, function () {
			var pagetitle = $elem.val();
			if ( !pagetitle ) {
				$statusField
					.removeClass( 'invalid' )
					.text( '' );
				return;
			}

			getPagesByTitle( pagetitle ).done( function ( data ) {
				var result = false;
				if ( data && data.query && !data.query.pages[ 0 ].missing ) {
					result = callback( data.query.pages );
				}

				if ( result ) {
					$statusField
						.removeClass( 'invalid' )
						.text( '' );
				} else {
					$statusField
						.addClass( 'invalid' )
						.text( mw.message( 'massmessage-parse-badpage', pagetitle ).text() );
				}
			} );
		} ) );
	}

	function isValidSpamList( pages ) {
		return pages[ 0 ].contentmodel === 'wikitext' ||
			pages[ 0 ].contentmodel === 'MassMessageListContent';
	}

	function isValidPageMessage( pages ) {
		return pages[ 0 ].contentmodel === 'wikitext';
	}

	// Only bind once for 'blur' so that the user can fill it in without errors;
	// after that, look at every change for immediate feedback.
	$spamlist.one( 'blur', function () {
		addPageTitleValidation( $( this ), isValidSpamList );
	} );

	$massmessagepage.one( 'blur', function () {
		addPageTitleValidation( $( this ), isValidPageMessage );
	} );

	// Autocomplete for spamlist titles
	autocomplete.enableTitleComplete( $spamlist );

	// Autocomplete for pages to send as message
	autocomplete.enableTitleComplete( $massmessagepage );
} );

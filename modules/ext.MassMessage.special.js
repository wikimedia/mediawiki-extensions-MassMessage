$( function () {
	'use strict';

	var badHtml = require( './ext.MassMessage.badhtml.js' ),
		$textbox = $( '#mw-massmessage-form-message textarea' );

	if ( !$textbox.length ) {
		return;
	}

	// Limit edit summaries to 240 bytes
	$( '#mw-massmessage-form-subject' ).byteLimit();

	badHtml( $textbox );

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
	 * @return {Object[]} Array of the jQuery element for the OOUI error message widget
	 *  and of the OOUI label inside the widget.
	 */
	function addStatusField( $elem ) {
		var message = new OO.ui.MessageWidget( {
			icon: 'error',
			type: 'error',
			inline: true,
			classes: [ 'mw-massmessage-form-error' ]
		} );
		message.$element.hide();
		$( $elem ).closest( '.mw-htmlform-field-HTMLTitleTextField' ).append( message.$element );
		return [ message.$element, message.$label ];
	}

	/**
	 * Validate the title in the form input field given
	 * in $elem. If the field is not valid, the function
	 * will show an appropriate error message under the
	 * input.
	 *
	 * @param $elem The jQuery element for the OOUI title field to be validated
	 * @param callback A callback function which is provided the list of pages by the title
	 *  and which returns a boolean value of whether the title is valid (true for valid).
	 * @param $statusField The OOUI error message for the field which may or may not be hidden
	 * @param $statusFieldLabel The label for the OOUI error message in $statusField
	 */
	function validateTitle( $elem, callback, $statusField, $statusFieldLabel ) {
		var pagetitle = $( 'input', $elem ).val();

		if ( !pagetitle ) {
			$statusField.hide();
			return;
		}

		getPagesByTitle( pagetitle ).done( function ( data ) {
			var result = false;
			if ( data && data.query && !data.query.pages[ 0 ].missing ) {
				result = callback( data.query.pages );
			}

			if ( result ) {
				$( $elem ).removeClass( 'oo-ui-flaggedElement-invalid' );
				$statusField.hide();
			} else {
				$( $elem ).addClass( 'oo-ui-flaggedElement-invalid' );
				if ( $elem.prop( 'id' ) === 'mw-massmessage-form-spamlist' ) {
					$( $statusFieldLabel ).text(
						mw.message( 'massmessage-parse-badspamlist', pagetitle ).text()
					);
				} else {
					$( $statusFieldLabel ).text(
						mw.message( 'massmessage-parse-badpage', pagetitle ).text()
					);
				}
				$statusField.show();
			}
		} );
	}

	/**
	 * Adds page title validation for a given text field
	 *
	 * @param {Object} $elem jQuery element to
	 * @param {Function} callback Called when we receive some pages in response.
	 */
	function addPageTitleValidation( $elem, callback ) {
		var $result = addStatusField( $elem );
		var $statusField = $result[ 0 ];
		var $statusFieldLabel = $result[ 1 ];
		validateTitle( $elem, callback, $statusField, $statusFieldLabel );
		var widget = OO.ui.infuse( $( $elem ) );
		widget.on(
			'change',
			OO.ui.debounce(
				function () {
					validateTitle( $elem, callback, $statusField, $statusFieldLabel );
				},
				250
			)
		);
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
	$( $( '#mw-massmessage-form-spamlist input' ) ).one( 'blur', function () {
		addPageTitleValidation( $( '#mw-massmessage-form-spamlist' ), isValidSpamList );
	} );

	$( $( '#mw-massmessage-form-page input' ) ).one( 'blur', function () {
		addPageTitleValidation( $( '#mw-massmessage-form-page' ), isValidPageMessage );
	} );
} );

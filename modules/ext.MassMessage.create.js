$( function () {
	'use strict';

	var checkTitle, checkSource, pageIsValidSource,
		checkSourceTimeout = -1,
		queryTitleApiRequest,
		titleWidget = OO.ui.infuse( $( '#mw-input-wptitle' ).closest( '.oo-ui-fieldLayout' ) ),
		sourceWidget = OO.ui.infuse( $( '#mw-input-wpsource' ).closest( '.oo-ui-fieldLayout' ) ),
		titleField = titleWidget.getField(),
		sourceField = sourceWidget.getField();

	checkTitle = function () {
		var title = titleField.getValue();
		if ( title ) {
			if ( queryTitleApiRequest ) {
				queryTitleApiRequest.abort();
				queryTitleApiRequest = undefined;
			}
			queryTitleApiRequest = ( new mw.Api() ).get( {
				action: 'query',
				prop: 'info',
				titles: title,
				formatversion: 2
			} ).done( function ( data ) {
				if ( data &&
					data.query &&
					data.query.pages &&
					!data.query.pages[ 0 ].missing
				) {
					// Page with title already exists
					titleWidget.setErrors( [ mw.message( 'massmessage-create-exists-short' ).text() ] );
				} else {
					// Clear validation error
					titleWidget.setErrors( [] );
				}
			} );
		} else {
			// Don't display an error if there is no input
			titleWidget.setErrors( [] );
		}
	};

	checkSource = function () {
		var source = sourceField.getValue();
		if ( source ) {
			( new mw.Api() ).get( {
				action: 'query',
				prop: 'info|categoryinfo',
				titles: source,
				formatversion: 2
			} ).done( function ( data ) {
				if ( pageIsValidSource( data ) ) {
					// Clear validation error
					sourceWidget.setErrors( [] );
				} else {
					sourceWidget.setErrors( [ mw.message( 'massmessage-create-invalidsource-short' ).text() ] );
				}
			} );
		} else {
			sourceWidget.setErrors( [] );
		}
	};

	pageIsValidSource = function ( response ) {
		var page;
		if ( !response || !response.query || !response.query.pages ) {
			return true; // ignore if the API acts up
		}
		if ( response.query.pages.length !== 1 ) {
			return false; // there should be exactly one page
		}
		page = response.query.pages[ 0 ];
		if ( page.ns === 14 ) {
			return Object.prototype.hasOwnProperty.call( page, 'categoryinfo' ); // non-empty category
		} else {
			return !page.missing &&
				( page.contentmodel === 'wikitext' ||
				page.contentmodel === 'MassMessageListContent' );
		}
	};

	// Warn if page title is already in use
	titleField.$input.one( 'blur', function () {
		checkTitle();
		titleField.on( 'change', checkTitle );
	} );

	// Warn if delivery list source is invalid
	sourceField.on( 'change', function () {
		// Debouncing - don't want to make an API call per request, nor give an error
		// when the user starts typing
		sourceWidget.setErrors( [] );
		clearTimeout( checkSourceTimeout );
		checkSourceTimeout = setTimeout( checkSource, 300 );
	} );

	// Uses the same method as ext.abuseFilter.edit.js from the AbuseFilter extension.
	var warnOnLeave,
		$form = $( '#mw-massmessage-create-form' ),
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

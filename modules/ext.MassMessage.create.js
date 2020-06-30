$( function () {
	'use strict';

	var autocomplete = require( './ext.MassMessage.autocomplete.js' ),
		checkTitle, checkSource, pageIsValidSource,
		checkSourceTimeout = -1,
		queryTitleApiRequest,
		$titleStatus = OO.ui.infuse( $( '#mw-input-wptitle' ).closest( '.oo-ui-fieldLayout' ) ),
		$sourceStatus = OO.ui.infuse( $( '#mw-input-wpsource' ).closest( '.oo-ui-fieldLayout' ) ),
		$formTitle = $titleStatus.getField(),
		$formSource = $sourceStatus.getField(),
		$formSourceTr = $formSource.$element.parent().parent();

	checkTitle = function () {
		var title = $formTitle.getValue();
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
					$titleStatus.setErrors( [ mw.message( 'massmessage-create-exists-short' ).text() ] );
				} else {
					// Clear validation error
					$titleStatus.setErrors( [] );
				}
			} );
		} else {
			// Don't display an error if there is no input
			$titleStatus.setErrors( [] );
		}
	};

	checkSource = function () {
		var source = $formSource.getValue();
		if ( source ) {
			( new mw.Api() ).get( {
				action: 'query',
				prop: 'info|categoryinfo',
				titles: source,
				formatversion: 2
			} ).done( function ( data ) {
				if ( pageIsValidSource( data ) ) {
					// Clear validation error
					$sourceStatus.setErrors( [] );
				} else {
					$sourceStatus.setErrors( [ mw.message( 'massmessage-create-invalidsource-short' ).text() ] );
				}
			} );
		} else {
			$sourceStatus.setErrors( [] );
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

	// Set the correct field state on load.
	if ( !$( '[value="import"][type="radio"]' ).is( ':checked' ) ) {
		$formSourceTr.hide(); // Progressive disclosure
	}

	$( '#mw-input-wpcontent' ).find( '[value="new"]' ).on( 'click', function () {
		$formSourceTr.hide();
	} );

	$( '#mw-input-wpcontent' ).find( '[value="import"]' ).on( 'click', function () {
		$formSourceTr.show();
	} );

	// Warn if page title is already in use
	$formTitle.$input.one( 'blur', function () {
		checkTitle();
		$formTitle.on( 'change', checkTitle );
	} );

	// Warn if delivery list source is invalid
	$formSource.$input.on( 'input autocompleteselect', function () {
		// Debouncing - don't want to make an API call per request, nor give an error
		// when the user starts typing
		$sourceStatus.setErrors( [] );
		clearTimeout( checkSourceTimeout );
		checkSourceTimeout = setTimeout( checkSource, 300 );
	} );

	autocomplete.enableTitleComplete( $formSource.$input );
} );

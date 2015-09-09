( function ( mw, $ ) {
	$( function () {
		/*global setTimeout, clearTimeout*/
		'use strict';

		var checkTitle, checkSource, pageIsValidSource,
			checkSourceTimeout = -1,
			$formTitle = $( '#mw-input-wptitle' ),
			$formSource = $( '#mw-input-wpsource' ),
			$formSourceTr = $formSource.parent().parent(),
			$titleStatus = $( '<span>' )
				.attr( 'id', 'mw-input-wptitle-status' )
				.insertAfter( $formTitle ),
			$sourceStatus = $( '<span>' )
				.attr( 'id', 'mw-input-wptitle-status' )
				.insertAfter( $formSource );

		checkTitle = function () {
			var title = $formTitle.val();
			if ( title ) {
				( new mw.Api() ).get( {
					action: 'query',
					prop: 'info',
					titles: title
				} ).done( function ( data ) {
					if ( data && data.query && !data.query.pages[ '-1' ] ) {
						// Page with title already exists
						$titleStatus.addClass( 'invalid' )
							.text( mw.message( 'massmessage-create-exists-short' ).text() );
					} else {
						// Clear validation error
						$titleStatus.removeClass( 'invalid' ).text( '' );
					}
				} );
			} else {
				// Don't display an error if there is no input
				$titleStatus.removeClass( 'invalid' ).text( '' );
			}
		};

		checkSource = function () {
			var source = $formSource.val();
			if ( source ) {
				( new mw.Api() ).get( {
					action: 'query',
					prop: 'info',
					titles: source
				} ).done( function ( data ) {
					if ( pageIsValidSource( data ) ) {
						// Clear validation error
						$sourceStatus.removeClass( 'invalid' ).text( '' );
					} else {
						$sourceStatus.addClass( 'invalid' )
							.text( mw.message( 'massmessage-create-invalidsource-short' ).text() );
					}
				} );
			} else {
				$sourceStatus.removeClass( 'invalid' ).text( '' );
			}
		};

		pageIsValidSource = function ( response ) {
			var i;
			if ( !response || !response.query ) {
				return true; // ignore if the API acts up
			}
			if ( response.query.pages[ '-1' ] ) {
				return false;
			}
			for ( i in response.query.pages ) {
				if ( response.query.pages[ i ].contentmodel === 'wikitext' ||
					response.query.pages[ i ].contentmodel === 'MassMessageListContent' ||
					response.query.pages[ i ].ns === 14 ) {
					return true;
				}
			}
			return false;
		};

		// Set the correct field state on load.
		if ( !$( '#mw-input-wpcontent-import' ).is( ':checked' ) ) {
			$formSourceTr.hide(); // Progressive disclosure
		}

		$( '#mw-input-wpcontent-new' ).click( function () {
			$formSourceTr.hide();
		} );

		$( '#mw-input-wpcontent-import' ).click( function () {
			$formSourceTr.show();
		} );

		// Warn if page title is already in use
		$formTitle.one( 'blur', function () {
			checkTitle();
			$formTitle.on( 'input autocompletechange', checkTitle );
		} );

		// Warn if delivery list source is invalid
		$formSource.on( 'input autocompleteselect', function () {
			// Debouncing - don't want to make an API call per request, nor give an error
			// when the user starts typing
			$sourceStatus.removeClass( 'invalid' ).text( '' );
			clearTimeout( checkSourceTimeout );
			checkSourceTimeout = setTimeout( checkSource, 300 );
		} );

		mw.massmessage.enableTitleComplete( $formSource );
	} );
}( mediaWiki, jQuery ) );

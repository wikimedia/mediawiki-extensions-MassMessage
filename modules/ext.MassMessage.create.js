( function ( mw, $ ) {
	$( function () {
		/*global setTimeout, clearTimeout*/
		'use strict';

		var $formImport = $( '#mw-input-wpcontent-import'),
			$formSource = $( '#mw-input-wpsource'),
			$formSourceTr = $formSource.parent().parent(),
			checkSourceTimeout = -1,
			checkSource, pageIsValidSource, showCreateError, removeCreateError;

		// Set the correct field state on load.
		if ( !$formImport.is( ':checked' ) ) {
			$formSourceTr.hide(); // Progressive disclosure
		}

		$( '#mw-input-wpcontent-new' ).click( function () {
			$formSourceTr.hide();
			removeCreateError( 'massmessage-create-invalidsource' );
		} );

		$formImport.click( function () {
			$formSourceTr.show();
		} );

		// Warn if page title is already in use
		$( '#mw-input-wptitle' ).blur( function () {
			( new mw.Api() ).get( {
				action: 'query',
				prop: 'info',
				titles: $( this ).val()
			} ).done( function ( data ) {
				if ( data && data.query ) {
					if ( !data.query.pages['-1'] ) {
						// Page with title already exists
						showCreateError( 'massmessage-create-exists' );
					} else {
						removeCreateError( 'massmessage-create-exists' );
					}
				}
			} );
		} );

		// Warn if delivery list source is invalid
		$formSource.on( 'input autocompleteselect', function () {
			// debouncing - don't want to make an API call per request, nor give an error
			// when the user starts typing
			removeCreateError( 'massmessage-create-invalidsource' );
			clearTimeout(checkSourceTimeout);
			checkSourceTimeout = setTimeout(checkSource, 300);
		} );

		checkSource = function () {
			( new mw.Api() ).get( {
				action: 'query',
				prop: 'info',
				titles: $formSource.val()
			} ).done( function ( data ) {
				if ( pageIsValidSource( data ) ) {
					removeCreateError( 'massmessage-create-invalidsource' );
				} else {
					showCreateError( 'massmessage-create-invalidsource' );
				}
			} );
		};

		pageIsValidSource = function ( response ) {
			var i;
			if( !response || !response.query ) {
				return true; // ignore if the API acts up
			}
			if( response.query.pages['-1'] ) {
				return false;
			}
			for ( i in response.query.pages ) {
				if( response.query.pages[i].contentmodel === 'wikitext' ||
					response.query.pages[i].contentmodel === 'MassMessageListContent' ||
					response.query.pages[i].ns === 14 ) {
					return true;
				}
			}
			return false;
		};

		// Show an error next to the create form
		showCreateError = function ( msgKey ) {
			var message = mw.message( msgKey ).escaped();
			if ( !$( 'div.error[data-key=\'' + msgKey + '\']' ).length ) {
				$( '.mw-htmlform-submit-buttons' ).prepend(
					$( '<div></div>' ).addClass( 'error' ).attr( 'data-key', msgKey )
						.html( '<p>' + message + '</p>' ).hide().fadeIn()
				);
			}
		};

		// Remove an error next to the create form
		removeCreateError = function ( msgKey ) {
			$( 'div.error[data-key=\'' + msgKey + '\']' ).remove();
		};

		mw.massmessage.enableTitleComplete( $formSource );
	} );
}( mediaWiki, jQuery ) );

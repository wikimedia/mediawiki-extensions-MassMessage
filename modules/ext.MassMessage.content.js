( function ( mw, $ ) {
	$( function () {
		/*global alert*/
		'use strict';

		var listShown = false;

		// Append an added page to the displayed list.
		var appendAdded = function ( title, site, missing ) {
			var targetAttribs, targetLink, removeLink;

			if ( !listShown ) {
				$( '#mw-massmessage-addedlist' ).show();
				listShown = true;
			}

			if ( site === '' ) {
				targetAttribs = {
					href: mw.util.getUrl( title ),
					title: title
				};
				if ( missing ) {
					targetAttribs['class'] = 'new';
				}
				targetLink = mw.html.element( 'a', targetAttribs, title );
			} else {
				targetAttribs = {
					href: '//' + site + mw.config.get( 'wgScript' ) + '?title=' +
						encodeURIComponent( title ),
					'class': 'external'
				};
				// Use a message so we have something like "<title> on <site>".
				targetLink = mw.message(
					'massmessage-content-addeditem',
					mw.html.element( 'a', targetAttribs, title ),
					site
				).text();
			}

			removeLink = mw.html.element( 'a', {
				'data-title': title,
				'data-site': ( site === '' ) ? 'local' : site,
				href: '#'
			}, mw.message( 'massmessage-content-remove' ).text() );

			$( '#mw-massmessage-addedlist ul' ).append(
				$( '<li></li>' ).append(
					$( '<span></span>' ).addClass( 'mw-massmessage-targetlink' ).html( targetLink ),
					$( '<span></span>' ).addClass( 'mw-massmessage-removelink' )
						.html( '(' + removeLink + ')' )
				).hide().fadeIn()
			);
		};

		// Return a target page in title or title@site (if site is not empty) form.
		var getApiParam = function ( title, site ) {
			var server, param;
			if ( site === '' ) {
				if ( title.indexOf( '@' ) >= 0 ) { // Handle titles containing '@'
					server = mw.config.get( 'wgServer' );
					param = title + '@' + server.substr( server.indexOf( '//' ) + 2 );
				} else {
					param = title;
				}
			} else {
				param = title + '@' + site;
			}
			return param;
		};

		// Handle remove links next to targets.
		$( '#mw-content-text' ).on( 'click', '.mw-massmessage-removelink a', function ( e ) {
			var param, $link = $( this );

			e.preventDefault();

			// TODO: Use jquery.confirmable once it's available.

			param = getApiParam(
				$link.attr( 'data-title' ),
				$link.attr( 'data-site' ) === 'local' ? '' : $link.attr( 'data-site' )
			);

			( new mw.Api() ).postWithToken( 'edit', {
				action: 'editmassmessagelist',
				spamlist: mw.config.get( 'wgPageName' ),
				remove: param
			} )
			.done( function () {
				// Treat as success if the page being removed could not be found.
				$link.closest( 'li' ).fadeOut();
			} )
			.fail( function ( errorCode ) {
				alert( mw.message( 'massmessage-content-removeerror', errorCode ).text() );
			} );
		} );

		// Show an error next to the add pages form.
		var showAddError = function ( msgKey, errorCode ) {
			var message;
			if ( errorCode === undefined ) {
				message = mw.message( msgKey ).escaped();
			} else {
				message = mw.message( msgKey, errorCode ).escaped();
			}
			$( '#mw-massmessage-addform' ).append(
				$( '<span></span>' ).addClass( 'error' ).html( message ).hide().fadeIn()
			);
		};

		// Handle add pages form.
		$( '#mw-massmessage-addform' ).submit( function( e ) {
			var title, site, apiResult, page;

			e.preventDefault();

			title = $.trim( $( '#mw-massmessage-addtitle' ).val() );
			site = $.trim( $( '#mw-massmessage-addsite' ).val() );
			if ( title === '' && site === '' ) {
				return; // Do nothing if there is no input
			}

			// Clear previous error messages.
			$( '#mw-massmessage-addform .error' ).remove();

			( new mw.Api() ).postWithToken( 'edit', {
				action: 'editmassmessagelist',
				spamlist: mw.config.get( 'wgPageName' ),
				add: getApiParam( title, site )
			} )
			.done( function ( data ) {
				apiResult = data.editmassmessagelist;

				if ( apiResult.result === 'Success' ) {
					if ( apiResult.added.length > 0 ) {
						page = apiResult.added[0];
						appendAdded(
							page.title,
							( 'site' in page ) ? page.site : '',
							( 'missing' in page ) ? true : false
						);
						// Clear the input fields
						$( '#mw-massmessage-addtitle' ).val( '' );
						$( '#mw-massmessage-addsite' ).val( '' );
					} else { // None added, i.e. it's already in the list
						showAddError( 'massmessage-content-alreadyinlist' );
					}
				} else { // The input was invalid.
					page = apiResult.invalidadd[0];
					if ( 'invalidtitle' in page && 'invalidsite' in page ) {
						showAddError( 'massmessage-content-invalidtitlesite' );
					} else if ( 'invalidtitle' in page ) {
						showAddError( 'massmessage-content-invalidtitle' );
					} else {
						showAddError( 'massmessage-content-invalidsite' );
					}
				}
			} )
			.fail( function ( errorCode ) {
				showAddError( 'massmessage-content-adderror', errorCode );
			} );
		} );
	} );
}( mediaWiki, jQuery ) );

( function ( mw, $ ) {
	$( function () {
		/*global alert*/
		'use strict';

		var listShown = false;

		// Append an added page to the displayed list.
		var appendAdded = function ( title, site ) {
			var targetLink, removeLink;

			if ( !listShown ) {
				$( '#mw-massmessage-addedlist' ).show();
				listShown = true;
			}

			if ( site === '' ) {
				targetLink = mw.html.element( 'a', {
					href: mw.util.getUrl( title ),
					title: title
				}, title );
			} else {
				// Use a message so we have something like "<title> on <site>".
				targetLink = mw.message(
					'massmessage-content-addeditem',
					mw.html.element( 'a', {
						href: '//' + site + mw.config.get( 'wgScript' ) + '?title=' +
							encodeURIComponent( title ),
						'class': 'external'
					}, title ),
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
				$link.closest( 'li' ).fadeOut();
			} )
			.fail( function ( errorCode ) {
				// TODO: Use something other than alert()?
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
			var title, site, param;

			e.preventDefault();

			title = $.trim( $( '#mw-massmessage-addtitle' ).val() );
			site = $.trim( $( '#mw-massmessage-addsite' ).val() );
			if ( title === '' && site === '' ) {
				return; // Do nothing if there is no input
			}

			// Clear previous error messages.
			$( '#mw-massmessage-addform .error' ).remove();

			param = getApiParam( title, site );
			( new mw.Api() ).postWithToken( 'edit', {
				action: 'editmassmessagelist',
				spamlist: mw.config.get( 'wgPageName' ),
				add: param
			} )
			.done( function ( data ) {
				if ( data.editmassmessagelist.added === 0 ) {
					// None added, i.e. it's already in the list
					showAddError( 'massmessage-content-alreadyinlist' );
				} else {
					appendAdded( title, site );
					// Clear the input fields
					$( '#mw-massmessage-addtitle' ).val( '' );
					$( '#mw-massmessage-addsite' ).val( '' );
				}
			} )
			.fail( function ( errorCode ) {
				if ( errorCode === 'invalidadd' ) {
					showAddError( 'massmessage-content-invalidadd' );
				} else {
					showAddError( 'massmessage-content-adderror', errorCode );
				}
			} );
		} );
	} );
}( mediaWiki, jQuery ) );

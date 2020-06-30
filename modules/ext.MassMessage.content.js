$( function () {
	'use strict';

	var autocomplete = require( './ext.MassMessage.autocomplete.js' ),
		listShown = false,
		appendAdded, getApiParam, removeHandler, confirmableParams, showAddError;

	// Append an added page to the displayed list.
	appendAdded = function ( title, site, missing ) {
		var targetAttribs, targetLink, removeLink, $list = $( '#mw-massmessage-addedlist ul' );

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
				targetAttribs.class = 'new';
			}
			targetLink = mw.html.element( 'a', targetAttribs, title );
		} else {
			targetAttribs = {
				href: '//' + site + mw.config.get( 'wgScript' ) + '?title=' +
					encodeURIComponent( title ),
				class: 'external'
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

		// If the list was empty, remove the message saying so.
		// eslint-disable-next-line no-jquery/no-sizzle
		if ( $list.children( ':visible' ).length === 0 ) {
			$list.prev( '.mw-massmessage-emptylist' ).remove();
		}

		$list.append(
			// FIXME: Use CSS transition
			// eslint-disable-next-line no-jquery/no-fade
			$( '<li>' ).append(
				$( '<span>' ).addClass( 'mw-massmessage-targetlink' ).html( targetLink ),
				$( '<span>' ).addClass( 'mw-massmessage-removelink' )
					.html( '(' + removeLink + ')' )
			).hide().fadeIn()
		);

		// Register the remove link handler again so it works on the new item.
		$list.find( '.mw-massmessage-removelink a' ).confirmable( confirmableParams );
	};

	// Return a target page in title or title@site (if site is not empty) form.
	getApiParam = function ( title, site ) {
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
	removeHandler = function ( e ) {
		var param, $link = $( this );

		e.preventDefault();

		param = getApiParam(
			$link.attr( 'data-title' ),
			$link.attr( 'data-site' ) === 'local' ? '' : $link.attr( 'data-site' )
		);

		( new mw.Api() ).postWithToken( 'csrf', {
			action: 'editmassmessagelist',
			spamlist: mw.config.get( 'wgPageName' ),
			remove: param
		} ).done( function () {
			// FIXME: Use CSS transition
			// eslint-disable-next-line no-jquery/no-fade
			$link.closest( 'li' ).fadeOut( 400, function () { // 400 is the default duration.
				var $list = $link.closest( 'ul' );

				// Replace empty lists with a message indicating the list is empty.
				// eslint-disable-next-line no-jquery/no-sizzle
				if ( $list.children( ':visible' ).length === 0 ) {
					$list.before(
						$( '<p>' ).addClass( 'mw-massmessage-emptylist' ).html(
							mw.message( 'massmessage-content-emptylist' ).escaped()
						)
					);
				}
			} );
		} ).fail( function ( errorCode ) {
			// eslint-disable-next-line no-alert
			alert( mw.message( 'massmessage-content-removeerror', errorCode ).text() );
		} );
	};

	// Parameters for jquery.confirmable (remove links)
	confirmableParams = {
		handler: removeHandler,
		i18n: {
			confirm: mw.message( 'massmessage-content-removeconf' ).escaped(),
			yes: mw.message( 'massmessage-content-removeyes' ).escaped(),
			no: mw.message( 'massmessage-content-removeno' ).escaped()
		}
	};

	// Show an error next to the add pages form.
	showAddError = function ( msgKey, errorCode ) {
		var message;
		if ( errorCode === undefined ) {
			// The following messages are used here:
			// * massmessage-content-alreadyinlist
			// * massmessage-content-invalidtitlesite
			// * massmessage-content-invalidtitle
			// * massmessage-content-invalidsite
			message = mw.message( msgKey ).escaped();
		} else {
			// The following messages are used here:
			// * massmessage-content-adderror
			// Since only 1 key is used, the lint rule fails
			// eslint-disable-next-line mediawiki/msg-doc
			message = mw.message( msgKey, errorCode ).escaped();
		}
		$( '#mw-massmessage-addform' ).append(
			// FIXME: Use CSS transition
			// eslint-disable-next-line no-jquery/no-fade
			$( '<span>' ).addClass( 'error' ).html( message ).hide().fadeIn()
		);
	};

	// Autocomplete for page titles
	autocomplete.enableTitleComplete( $( '#mw-massmessage-addtitle' ) );

	// Autocomplete for sites
	autocomplete.enableSiteComplete( $( '#mw-massmessage-addsite' ) );

	// Register handler for remove links.
	$( '.mw-massmessage-removelink a' ).confirmable( confirmableParams );

	// Handle add pages form.
	$( '#mw-massmessage-addform' ).on( 'submit', function ( e ) {
		var title, site, apiResult, page,
			$site = $( '#mw-massmessage-addsite' );

		e.preventDefault();

		title = $( '#mw-massmessage-addtitle' ).val().trim();
		if ( $site.length ) {
			site = $site.val().trim();
		} else {
			site = '';
		}
		if ( title === '' && site === '' ) {
			return; // Do nothing if there is no input
		}

		// Clear previous error messages.
		$( '#mw-massmessage-addform .error' ).remove();

		( new mw.Api() ).postWithToken( 'csrf', {
			action: 'editmassmessagelist',
			spamlist: mw.config.get( 'wgPageName' ),
			add: getApiParam( title, site )
		} ).done( function ( data ) {
			apiResult = data.editmassmessagelist;

			if ( apiResult.result === 'Success' ) {
				if ( apiResult.added.length > 0 ) {
					page = apiResult.added[ 0 ];
					appendAdded(
						page.title,
						( 'site' in page ) ? page.site : '',
						'missing' in page
					);
					// Clear the input fields
					$( '#mw-massmessage-addtitle' ).val( '' );
					$( '#mw-massmessage-addsite' ).val( '' );
				} else { // None added, i.e. it's already in the list
					showAddError( 'massmessage-content-alreadyinlist' );
				}
			} else { // The input was invalid.
				page = apiResult.invalidadd[ 0 ];
				if ( 'invalidtitle' in page && 'invalidsite' in page ) {
					showAddError( 'massmessage-content-invalidtitlesite' );
				} else if ( 'invalidtitle' in page ) {
					showAddError( 'massmessage-content-invalidtitle' );
				} else {
					showAddError( 'massmessage-content-invalidsite' );
				}
			}
		} ).fail( function ( errorCode ) {
			showAddError( 'massmessage-content-adderror', errorCode );
		} );
	} );
} );

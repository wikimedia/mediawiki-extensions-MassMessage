$( () => {
	'use strict';

	let listShown = false;
	const formLayout = OO.ui.infuse( $( '#mw-massmessage-addform' ) ),
		titleWidget = OO.ui.infuse( $( '#mw-massmessage-addtitle' ) ),
		titleField = titleWidget.getField(),
		$site = $( '#mw-massmessage-addsite' );

	let confirmableParams = null;

	let siteWidget, siteField;
	if ( $site.length ) {
		siteWidget = OO.ui.infuse( $site );
		siteField = siteWidget.getField();
	}

	// Append an added page to the displayed list.
	const appendAdded = function ( title, site, missing ) {
		const $list = $( '#mw-massmessage-addedlist ul' );

		if ( !listShown ) {
			$( '#mw-massmessage-addedlist' ).show();
			listShown = true;
		}

		let targetLink;
		if ( site === '' ) {
			const targetAttribs = {
				href: mw.util.getUrl( title ),
				title: title
			};
			if ( missing ) {
				targetAttribs.class = 'new';
			}
			targetLink = mw.html.element( 'a', targetAttribs, title );
		} else {
			const targetAttribs = {
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

		const removeLink = mw.html.element( 'a', {
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
	const getApiParam = function ( title, site ) {
		let server, param;
		if ( site === '' ) {
			if ( title.includes( '@' ) ) { // Handle titles containing '@'
				server = mw.config.get( 'wgServer' );
				param = title + '@' + server.slice( server.indexOf( '//' ) + 2 );
			} else {
				param = title;
			}
		} else {
			param = title + '@' + site;
		}
		return param;
	};

	// Handle remove links next to targets.
	const removeHandler = function ( e ) {
		const $link = $( this );

		e.preventDefault();

		const param = getApiParam(
			$link.attr( 'data-title' ),
			$link.attr( 'data-site' ) === 'local' ? '' : $link.attr( 'data-site' )
		);

		( new mw.Api() ).postWithToken( 'csrf', {
			action: 'editmassmessagelist',
			spamlist: mw.config.get( 'wgPageName' ),
			remove: param
		} ).done( () => {
			// FIXME: Use CSS transition
			// eslint-disable-next-line no-jquery/no-fade
			$link.closest( 'li' ).fadeOut( 400, () => { // 400 is the default duration.
				const $list = $link.closest( 'ul' );

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
		} ).fail( ( errorCode ) => {
			OO.ui.alert( mw.message( 'massmessage-content-removeerror', errorCode ).text() );
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

	// Register handler for remove links.
	$( '.mw-massmessage-removelink a' ).confirmable( confirmableParams );

	// Handle add pages form.
	formLayout.on( 'submit', () => {
		const title = titleField.getValue().trim();
		let site;
		if ( siteField ) {
			site = siteField.getValue().trim();
		} else {
			site = '';
		}
		if ( title === '' && site === '' ) {
			return; // Do nothing if there is no input
		}

		// Clear previous error messages.
		titleWidget.setErrors( [] );
		if ( siteWidget ) {
			siteWidget.setErrors( [] );
		}

		( new mw.Api() ).postWithToken( 'csrf', {
			action: 'editmassmessagelist',
			spamlist: mw.config.get( 'wgPageName' ),
			add: getApiParam( title, site )
		} ).done( ( data ) => {
			const apiResult = data.editmassmessagelist;

			if ( apiResult.result === 'Success' ) {
				if ( apiResult.added.length > 0 ) {
					const page = apiResult.added[ 0 ];
					appendAdded(
						page.title,
						( 'site' in page ) ? page.site : '',
						'missing' in page
					);
					// Clear the input fields
					titleField.setValue( '' );
					if ( siteField ) {
						siteField.setValue( '' );
					}
				} else { // None added, i.e. it's already in the list
					titleWidget.setErrors( [ mw.msg( 'massmessage-content-alreadyinlist' ) ] );
				}
			} else { // The input was invalid.
				const page = apiResult.invalidadd[ 0 ];
				if ( 'invalidtitle' in page ) {
					titleWidget.setErrors( [ mw.msg( 'massmessage-content-invalidtitle' ) ] );
				}
				if ( 'invalidsite' in page && siteWidget ) {
					siteWidget.setErrors( [ mw.msg( 'massmessage-content-invalidsite' ) ] );
				}
			}
		} ).fail( ( errorCode ) => {
			titleWidget.setErrors( [ mw.msg( 'massmessage-content-adderror', errorCode ) ] );
		} );
	} );
} );

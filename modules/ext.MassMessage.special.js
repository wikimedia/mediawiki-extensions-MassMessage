( function ( mw, $ ) {
	$( function () {
		'use strict';

		// Dynamic page title validation
		var $spamlist = $( '#mw-massmessage-form-spamlist' ),
			$spamliststatus = $( '<span>' )
				.attr( 'id', 'mw-massmessage-form-spamlist-status' )
				.insertAfter( $spamlist );

		// Limit edit summaries to 240 bytes
		$( '#mw-massmessage-form-subject' ).byteLimit();

		function checkPageTitle() {
			var api = new mw.Api(),
				pagetitle = $spamlist.val();
			if ( pagetitle ) {
				api.get( {
					action: 'query',
					titles: pagetitle,
					prop: 'info',
					formatversion: 2
				} ).done( function ( data ) {
					if ( data && data.query &&
						// If the page exists and has a supported content model
						( !data.query.pages[ 0 ].missing &&
							data.query.pages[ 0 ].contentmodel === 'wikitext' ||
							data.query.pages[ 0 ].contentmodel === 'MassMessageListContent' ) ||
						// Or if the text refers to a category
						data.query.pages[ 0 ].ns === 14
					) {
						// No error message is displayed
						$spamliststatus
							.removeClass( 'invalid' )
							.text( '' );
					} else {
						// Otherwise, display an error notice
						$spamliststatus
							.addClass( 'invalid' )
							.text( mw.message( 'massmessage-parse-badpage', pagetitle ).text() );
					}
				} );
			} else {
				// If no text is entered, don't display any warning
				$spamliststatus
					.removeClass( 'invalid' )
					.text( '' );
			}
		}

		// Only bind once for 'blur' so that the user can fill it in without errors;
		// after that, look at every change for immediate feedback.
		$spamlist.one( 'blur', function () {
			$spamlist.on( 'input autocompletechange', checkPageTitle );
		} );

		// Autocomplete for spamlist titles
		mw.massmessage.enableTitleComplete( $spamlist );
	} );
}( mediaWiki, jQuery ) );

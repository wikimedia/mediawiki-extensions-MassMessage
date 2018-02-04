/**
 * Attempt to detect invalid HTML
 * Based on http://www.raymondcamden.com/index.cfm/2012/1/23/Detecting-invalid-HTML-with-JavaScript
 * Similar PHP method that checks on preview in SpecialMassMessage.php
 */
( function ( mw, $ ) {
	$( function () {
		'use strict';
		var voidElements, $msg, $warnings;

		// Construct a set containing elements that do not need an end tag.
		// List obtained from http://www.w3.org/TR/html-markup/syntax.html#syntax-elements
		voidElements = { area: 1, base: 1, br: 1, col: 1, command: 1,
			embed: 1, hr: 1, img: 1, input: 1, keygen: 1, link: 1,
			meta: 1, param: 1, source: 1, track: 1, wbr: 1 };

		$msg = $( '#mw-massmessage-form-message' );
		$warnings = $( '<div>' )
			.attr( 'id', 'mw-massmessage-form-warnings' )
			.addClass( 'warningbox' );
		$msg.after( $warnings );
		$warnings.hide();

		$msg.on( 'keyup', $.debounce( 500, function () {
			var code, matches, tags, results, tag;

			code = $.trim( $msg.val() );
			if ( code === '' ) {
				$warnings.hide();
				return;
			}

			// Ignore tags that have '/' outside of the first character
			// (assume those are self closing).
			matches = code.match( /<[\w/][^/]*?>/g );
			if ( !matches ) {
				$warnings.hide();
				return;
			}

			tags = {};
			$.each( matches, function ( idx, itm ) {
				var realTag, tag;

				// Keep just the element names and the starting '/', if exists.
				tag = itm.replace( /[<>]/g, '' ).split( ' ' )[ 0 ];
				if ( tag.charAt( 0 ) !== '/' ) { // Start tag
					if ( !voidElements.hasOwnProperty( tag ) ) { // Ignore void elements
						if ( tags.hasOwnProperty( tag ) ) {
							tags[ tag ]++;
						} else {
							tags[ tag ] = 1;
						}
					}
				} else { // End tag
					realTag = tag.substr( 1, tag.length );
					if ( tags.hasOwnProperty( realTag ) ) {
						tags[ realTag ]--;
					} else {
						tags[ realTag ] = -1;
					}
				}
			} );

			results = [];
			for ( tag in tags ) {
				if ( tags[ tag ] > 0 ) {
					results.push( '<' + tag + '>' );
				} else if ( tags[ tag ] < 0 ) {
					results.push( '</' + tag + '>' );
				}
			}
			if ( results.length > 0 ) {
				$warnings.show();
				$warnings.text( mw.message( 'massmessage-badhtml', results.join( ', ' ), results.length ).text() );
			} else {
				$warnings.hide();
			}
		} ) );
	} );

}( mediaWiki, jQuery ) );

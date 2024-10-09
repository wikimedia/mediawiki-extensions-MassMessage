/**
 * Attempt to detect invalid HTML
 * Based on http://www.raymondcamden.com/index.cfm/2012/1/23/Detecting-invalid-HTML-with-JavaScript
 * Similar PHP method that checks on preview in SpecialMassMessage.php
 */
'use strict';

/**
 * @param {jQuery} $msg
 */
function badHtml( $msg ) {
	var voidElements, $warnings;

	// Construct a set containing HTML singleton elements (do not need an end tag).
	// List obtained from http://www.w3.org/TR/html-markup/syntax.html#syntax-elements
	voidElements = { area: 1, base: 1, br: 1, col: 1, command: 1,
		embed: 1, hr: 1, img: 1, input: 1, keygen: 1, link: 1,
		meta: 1, param: 1, source: 1, track: 1, wbr: 1 };

	$warnings = $( '<div>' )
		.attr( 'id', 'mw-massmessage-form-warnings' );

	$msg.after( $warnings );
	$warnings.hide();

	$msg.on( 'keyup', OO.ui.debounce( function () {
		var code, matches, tags, results, tagName;

		code = $msg.val().trim();
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
		matches.forEach( function ( itm ) {
			var realTag, tag,
				hasOwn = Object.prototype.hasOwnProperty;

			// Keep just the element names and the starting '/', if exists.
			tag = itm.replace( /[<>]/g, '' ).split( ' ' )[ 0 ];
			if ( tag.charAt( 0 ) !== '/' ) { // Start tag
				if ( !hasOwn.call( voidElements, tag ) ) { // Ignore void elements
					if ( hasOwn.call( tags, tag ) ) {
						tags[ tag ]++;
					} else {
						tags[ tag ] = 1;
					}
				}
			} else { // End tag
				realTag = tag.slice( 1, 1 + tag.length );
				if ( hasOwn.call( tags, realTag ) ) {
					tags[ realTag ]--;
				} else {
					tags[ realTag ] = -1;
				}
			}
		} );

		results = [];
		for ( tagName in tags ) {
			if ( tags[ tagName ] > 0 ) {
				results.push( '<' + tagName + '>' );
			} else if ( tags[ tagName ] < 0 ) {
				results.push( '</' + tagName + '>' );
			}
		}
		if ( results.length > 0 ) {
			$warnings.show();
			$warnings.empty().append(
				mw.util.messageBox(
					mw.message( 'massmessage-badhtml', results.join( ', ' ), results.length ).text(),
					'warning'
				)
			);
		} else {
			$warnings.hide();
		}
	}, 500 ) );
}

module.exports = badHtml;

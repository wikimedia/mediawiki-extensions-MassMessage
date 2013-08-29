// Limit edit summaries to 240 bytes
// Modified from mediawiki-core/resources/mediawiki.special/mediawiki.special.movePage.js
jQuery( function ( $ ) {
	$( '#mw-massmessage-form-subject' ).byteLimit();
} );

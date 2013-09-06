/**
 * Attempt to detect invalid HTML
 * from http://www.raymondcamden.com/index.cfm/2012/1/23/Detecting-invalid-HTML-with-JavaScript
 */
( function ( mw, $ ) {
    $( function () {
        $( '#mw-massmessage-form-message' ).delayedBind( 500, 'keyup', function( ) {
            var code, regex, matches, tags, possibles, tag;
            code = $.trim( $( '#mw-massmessage-form-message' ).val() );
            if( code === '' ) {
                return;
            }

            regex = /<.*?>/g;
            matches = code.match(regex);
            if( !matches.length ) {
                return;
            }

            tags = {};

            $.each(matches, function( idx, itm ) {
                var realTag, tag;
                //if the tag is, <..../>, it's self closing
                if ( itm.substr( itm.length - 2, itm.length ) !== '/>' ) {

                    //strip out any attributes
                    tag = itm.replace(/[<>]/g, '').split(' ')[0];
                    //start or end tag?
                    if ( tag.charAt(0) !== '/' ) {
                        if ( tags.hasOwnProperty( tag ) ) {
                            tags[tag]++;
                        } else {
                            tags[tag] = 1;
                        }
                    } else {
                        realTag = tag.substr(1, tag.length);
                        if (tags.hasOwnProperty(realTag)) {
                            tags[realTag]--;
                        } else {
                            tags[realTag] = -1;
                        }
                    }
                }
            });

            possibles = [];
            for ( tag in tags ) {
                if ( tags[tag] !== 0 ) {
                    possibles.push( '<' + tag + '>' );
                }
            }
            if (possibles.length) {
                mw.notify(
                    mw.message( 'massmessage-badhtml', possibles.join(', '), possibles.length ).text(),
                    { tag: 'massmessage-html-warning' }  // Show only one notification at a time
                );
            }
        });
    });

}( mediaWiki, jQuery ) );

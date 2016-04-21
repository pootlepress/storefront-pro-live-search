/**
 * @developer wpdevelopment.me <shramee@wpdvelopment.me>
 */

( function ( $ ) {
	$( '.wc-live-search-field' ).keyup( function () {
		var $t = $( this ),
			val = $t.val();
		if ( 3 > val.length ) {
			return;
		}
		var $r = $t.siblings( '.wc-live-search-results' );
		
	} );
} )( jQuery );

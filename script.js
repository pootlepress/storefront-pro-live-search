/**
 * @developer wpdevelopment.me <shramee@wpdvelopment.me>
 */

( function ( $ ) {
	var timeOut;
	$( '.sfp-live-search-field' ).keyup( function () {

		var $t = $( this ),
			val = $t.val(),
			$r = $t.siblings( '.sfp-live-search-results' );

		if ( timeOut ) clearTimeout( timeOut );
		timeOut = null;

		if ( 3 > val.length ) {
			if ( 1 > val.length ) {
				$r.html( '' );
			}
			return;
		}

		timeOut = setTimeout( function () {
			$.ajax( {
					method: 'GET',
					url   : wclsAjax.url + '?s=' + val,
				} )
				.done( function ( r ) {
					$r.html( '' );
					if ( typeof r === 'string' ) {
						r = JSON.parse( r );
					}
					$.each( r, function ( k, e ) {
						var $cnt = $( '<div><h3>' + k + '</h3></div>' );
						$.each( e, function ( l, html ) {
							$cnt.append( html );
						} );
						$r.append( $cnt );
					} );
				} );
		}, 700 );
	} );
} )( jQuery )
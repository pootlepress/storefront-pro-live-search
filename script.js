/**
 * @developer wpdevelopment.me <shramee@wpdvelopment.me>
 */

(
	function ( $ ) {
		var ajaxRef;
		$( '.sfp-live-search-field' ).keyup( function () {

			var $t = $( this ),
				val = $t.val(),
				$r = $t.siblings( '.sfp-live-search-results' );

			if ( ajaxRef && ajaxRef.abort ) {
				ajaxRef.abort();
			}
			timeOut = null;

			if ( 3 > val.length ) {
				if ( 1 > val.length ) {
					$r.html( '' );
				}
				return;
			}

			ajaxRef = $
				.ajax( {
					method: 'GET',
					url: wclsAjax.url + '?s=' + encodeURIComponent(val),
				} )
				.done( function ( r ) {
					$r.html( '' );
					if ( typeof r === 'string' ) {
						r = JSON.parse( r );
					}
					if ( r ) {
						$.each( r, function ( k, e ) {
							var $cnt = $( '<div><h3>' + k + '</h3></div>' );
							$.each( e, function ( l, html ) {
								$cnt.append( html );
							} );
							$r.append( $cnt );
						} );
					} else {
						console.log( r )
					}
				} );
		} );
	}
)( jQuery );
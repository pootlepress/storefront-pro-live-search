/**
 * @developer wpdevelopment.me <shramee@wpdvelopment.me>
 */

(
	function ( $ ) {
		var ajaxRef, lastResponse, $r;
		$( '.sfp-live-search-field' ).keyup( function () {

			var $t = $( this ),
				val = $t.val();
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

			var keyword = encodeURIComponent( val ).toLowerCase();

			$r.html( '' );
			$.each( lastResponse, function ( k, e ) {
				if ( 0 !== k.indexOf( '_' ) ) {
					var $cnt = $( '<div><h3>' + k + '</h3></div>' );
					$.each( e, function ( l, itm ) {
						if ( itm.title.toLowerCase().indexOf( keyword ) > - 1 ) {
							addItem( itm, $cnt )
						}
					} );
					$r.append( $cnt );
				}
			} );

			ajaxRef = $
				.ajax( {
					method: 'GET',
					url: wclsAjax.url + '?s=' + keyword,
				} )
				.done( parseResp );

		} );

		function addItem( itm, $cnt ) {
			$cnt.append(
				"<a class='wcls-prod' href='" + itm.url + "'>" +
				( itm.img ? "<img src='" + lastResponse._url + '/' + itm.img + "'>" : '' ) +
				itm.title + "</a>"
			);
		}

		parseResp = function( r ) {
			$r.html( '' );
			if ( typeof r === 'string' ) {
				r = JSON.parse( r );
			}
			if ( r ) {
				lastResponse = r;
				$.each( r, function ( k, e ) {
					if ( 0 !== k.indexOf( '_' ) ) {
						var $cnt = $( '<div><h3>' + k + '</h3></div>' );
						$.each( e, function ( l, itm ) {
							addItem( itm, $cnt )
						} );
					}
					$r.append( $cnt );
				} );
			} else {
				console.log( r )
			}
		}
	}
)( jQuery );
;( function ( $ ) {
	
	
	$.fn.gekoPagination = function() {
		
		if ( 'string' == $.type( arguments[ 0 ] ) ) {
			
			var method = arguments[ 0 ];
			
			if ( method = 'setpage' ) {
				
				var idx = parseInt( arguments[ 1 ] );
				
				return this.each( function() {
					
					var div = $( this );
					
					var iNumSlides = div.data( 'pagination_num_slides' );
					var iPadItems = div.data( 'pagination_pad_items' );
					var iMaxItems = div.data( 'pagination_max_items' );
					
					//// reset
					div.find( 'a.page' ).removeClass( 'current' );
					div.find( 'a.first, a.last, span.extend.prev, span.extend.next, a.previouspostslink, a.nextpostslink, a.page' ).hide();
					
					//// set
					if ( iNumSlides > 1 ) {
					
						div.find( '.navigation a[data-idx=' +  idx + ']' ).addClass( 'current' );
						
						if ( idx > 0 ) {
							div.find( 'a.previouspostslink' ).show();
						}
						
						if ( idx < ( iNumSlides - 1 ) ) {
							div.find( 'a.nextpostslink' ).show();
						}
						
						if ( iMaxItems < iNumSlides ) {
							
							var iStart = null;
							
							if ( idx <= iPadItems ) {
								// head
								iStart = 0;
								div.find( 'a.last, span.extend.next' ).show();
							} else if ( idx >= ( iNumSlides - iPadItems - 1 ) ) {
								// tail
								iStart = iNumSlides - iMaxItems;
								div.find( 'a.first, span.extend.prev' ).show();
							} else {
								// mid
								iStart = idx - iPadItems;
								div.find( 'a.first, a.last, span.extend.prev, span.extend.next' ).show();
							}
							
							var i = 0;
							for ( i = iStart; i < ( iStart + iMaxItems ); i++ ) {
								div.find( '.navigation a[data-idx=' +  i + ']' ).show();
							}
							
						} else {
							div.find( 'a.page' ).show();
						}
					}
					
				} );

			}
			
		} else {
			
			// default
			
			var opts = $.extend( {
				
				numSlides: null,
				padItems: 3,
				
				pageGoTo: function( idx ) { },
				pagePrev: function() { },
				pageNext: function() { },
				pageFirst: function() { },
				pageLast: function() { }
				
			}, arguments[ 0 ] );
			
			return this.each( function() {
				
				var div = $( this );
				
				var iNumSlides = opts.numSlides;
				if ( null === iNumSlides ) {
					iNumSlides = div.find( 'a.page' ).length;
				}
				
				div.data( 'pagination_num_slides', iNumSlides );
				div.data( 'pagination_pad_items', opts.padItems );
				div.data( 'pagination_max_items', ( parseInt( opts.padItems ) * 2 ) + 1 );
				
				//
				div.find( 'a.page' ).click( function() {
					
					var a = $( this );
					var idx = parseInt( a.attr( 'data-idx' ) );
					
					opts.pageGoTo( idx );
					
					return false;
				} );
				
				//
				div.find( 'a.previouspostslink' ).click( function() {
					opts.pagePrev();
					return false;
				} );
				
				//
				div.find( 'a.nextpostslink' ).click( function() {
					opts.pageNext();
					return false;
				} );
				
				//
				div.find( 'a.first' ).click( function() {
					opts.pageFirst();
					return false;
				} );
				
				//
				div.find( 'a.last' ).click( function() {
					opts.pageLast();
					return false;
				} );
				
			} );
		
		}
		
	};

} )( jQuery );

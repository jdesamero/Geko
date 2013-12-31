;( function ( $ ) {
	
	$.fn.gekoSlideshow = function( options ) {
		
		var opts = $.extend( {
			delay: 20,								// seconds
			fade_duration: 1,						// seconds
			gly_item_sel: '.gly_item',				// div
			gly_ctls_sel: '.gly_ctls',				// div
			gly_btn_class: 'gly_btn',				// div
			gly_cur_class: 'gly_cur',
			gly_item_id_prefix: null,
			gly_item_id_bind: [],
			gly_btn_ext_class: 'gly_btn_ext',
			gly_item_class_bind: null
		}, options );
		
		/* /
		alert(
			opts.delay + ' - ' + 
			opts.gly_item_sel + ' - ' + 
			opts.gly_btn_class
		);
		/* */
		
		var gallery = $( this );
		var gly_items = $( this ).find( opts.gly_item_sel );
		var gly_items_bound = [];
		
		if ( opts.gly_item_class_bind ) {
			$.each( opts.gly_item_class_bind, function() {
				if ( this.inside ) {
					gly_items_bound.push( gallery.find( '.' + this.cls ) );
				} else {
					gly_items_bound.push( $( '.' + this.cls ) );				
				}
			} );
		}
		
		var max_idx = parseInt( gly_items.length ) - 1;
		var cur_idx = max_idx;										// current index in the slideshow
		var cur_timeout = null;
		
		var fAdvanceSlide = function() {
			
			if ( cur_idx >= max_idx ) {
				cur_idx = 0;
			} else {
				cur_idx = parseInt( cur_idx ) + 1;
			}
			
			fSlideEffect();
			
			if ( max_idx > 0 ) {
				fAutoPlay();
			}
		};
		
		var fAutoPlay = function() {
			cur_timeout = setTimeout(
				function() {
					fAdvanceSlide();
				},
				parseInt( opts.delay * 1000 )
			);
		};
		
		var fSlideEffect = function() {
			
			gallery.find( '.' + opts.gly_btn_class ).each( function() {
				if ( $( this ).data( 'idx' ) == cur_idx ) {
					$( this ).addClass( opts.gly_cur_class );
				} else {
					$( this ).removeClass( opts.gly_cur_class );
				}
			} );
			
			// externally bound buttons
			if ( opts.gly_item_id_prefix ) {
				$( '.' + opts.gly_btn_ext_class ).each( function() {
					if ( $( this ).data( 'idx' ) == cur_idx ) {
						$( this ).addClass( opts.gly_cur_class );
					} else {
						$( this ).removeClass( opts.gly_cur_class );
					}				
				} );
			}
			
			var iFadeDuration = parseInt( opts.fade_duration * 1000 );
			
			gly_items.filter( ':not(:animated)' ).fadeOut( iFadeDuration );
			$( gly_items[ cur_idx ] ).filter( ':not(:animated)' ).fadeIn( iFadeDuration );
			
			$.each( gly_items_bound, function() {
				
				this.filter( ':not(:animated)' ).fadeOut( iFadeDuration );
				$( this[ cur_idx ] ).filter( ':not(:animated)' ).fadeIn( iFadeDuration );
				
			} );
			
		};
		
		var fClickButton = function() {
			
			var btn_idx = $( this ).data( 'idx' );
			
			if ( btn_idx != cur_idx ) {
				cur_idx = btn_idx;
				fSlideEffect();
				
				if ( cur_timeout ) {
					clearTimeout( cur_timeout );
					fAutoPlay();
				}
			}
			
		};
		
		gly_items.hide();

		$.each( gly_items_bound, function() {
			this.hide();			
		} );
		
		// externally bound buttons
		if ( opts.gly_item_id_prefix ) {
			
			gly_items.each( function( i ) {
				
				var id = $( this ).attr( 'id' );
				if ( id ) {
					$.each( opts.gly_item_id_bind, function() {
						
						$( '#' + id.replace( opts.gly_item_id_prefix, this ) ).each( function() {
							
							$( this ).data( 'idx', i );
							$( this ).click( fClickButton );
							$( this ).addClass( opts.gly_btn_ext_class );
							
							if ( 'a' == this.nodeName.toLowerCase() ) return false;
							
						} );
						
					} );
				}
				
			} );
			
		}
		
		gallery.find( opts.gly_ctls_sel ).each( function() {
			
			var ctl_div = $( this );
			var ctl_btns = ctl_div.find( '.' + opts.gly_btn_class );
			
			// check for existing buttons
			if ( ctl_btns.length > 0 ) {
				
				// assign functionality to existing controls
				ctl_btns.each( function( i ) {
					
					$( this ).data( 'idx', i );
					$( this ).click( fClickButton );
					
					if ( 'a' == this.nodeName.toLowerCase() ) return false;
					
				} );
				
			} else {
				
				// dynamically create button controls
				gly_items.each( function( i ) {
					
					var gly_btn = $( '<div class="' +  opts.gly_btn_class + '" ><\/div>' );
					gly_btn.data( 'idx', i );
					gly_btn.click( fClickButton );
					
					ctl_div.append( gly_btn );
					
					if ( 'a' == this.nodeName.toLowerCase() ) return false;
					
				} );
				
			}
			
		} );
					
		// initialize slideshow
		fAdvanceSlide();
				
		return this;		
	};
	
} )( jQuery );
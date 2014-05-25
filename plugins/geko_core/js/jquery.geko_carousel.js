;( function ( $ ) {


	// Page tracker
	
	var PageTracker = function() {
		
		var _this = this;
		
		var iPageCount = 0;
		var iCurPage = 0;
		var bNoMorePages = false;
		
		
		this.goNext = function() {
			iCurPage++;
		};
		
		this.goPrev = function() {
			iCurPage--;
		};
		
		this.onLastPage = function() {
			return ( iPageCount == iCurPage ) ? true : false ;
		};
		
		this.addPage = function() {
			iPageCount++;
		};
		
		this.getPageNum = function() {
			return iCurPage;
		};
		
		this.setNoMorePages = function() {
			bNoMorePages = true;
		};
		
		this.getNoMorePages = function() {
			return bNoMorePages;
		};
		
	};
	
	
	$.fn.gekoCarousel = function( options ) {
		
		var opts = $.extend( {
			
			scroll: 1,
			
			clip_sel: '.jcarousel-clip',
			prev_sel: '.jcarousel-prev',
			next_sel: '.jcarousel-next',
			
			disabled_class: 'disabled',
			
			load_more_cb: null
			
		}, options );
		
		
		//
		return this.each( function() {
			
			var oPt = new PageTracker();
			
			var eCarWrap = $( this );
			
			var eCarousel = eCarWrap.find( opts.clip_sel );
			
			var eCaroPrev = eCarWrap.find( opts.prev_sel );
			var eCaroNext = eCarWrap.find( opts.next_sel );
			
			
			//// initialization function
			
			var fCarInit = function() {
				
				// actual carousel
				
				eCarousel.jcarousel();
				
				
				// prev/next options
				
				var oPrevOpts = {
					target: '-=%d'.printf( opts.scroll )
				};
				
				var oNextOpts = {
					target: '+=%d'.printf( opts.scroll )
				};
				
				if ( opts.load_more_cb ) {
					
					// include ajax loading options
					
					oPrevOpts.method = function() {
						oPt.goPrev();
						this.carousel().jcarousel( 'scroll', this.options( 'target' ), true );
					};
					
					oNextOpts.method = function() {

						oPt.goNext();
						
						var oControl = this;
						var eCaro = this.carousel();
						
						if ( opts.load_more_cb && oPt.onLastPage() && !oPt.getNoMorePages() ) {
							
							// load by ajax
							opts.load_more_cb( eCarWrap, oPt, function() {
								
								eCaro.jcarousel( 'reload' );
								eCaro.jcarousel( 'scroll', oControl.options( 'target' ), true );
							
							}, function() {
								
								eCaroNext.addClass( opts.disabled_class );
								
							} );
							
						} else {
							
							// already loaded
							eCaro.jcarousel( 'scroll', oControl.options( 'target' ), true );
						}
						
					}
					
				}
				
				
				// prev control
				
				
				eCaroPrev.on( 'jcarouselcontrol:active', function() {
					
					$( this ).removeClass( opts.disabled_class );
				
				} ).on( 'jcarouselcontrol:inactive', function() {
					
					$( this ).addClass( opts.disabled_class );
					
				} ).jcarouselControl( oPrevOpts );
				
				
				// next control
				
				eCaroNext.on( 'jcarouselcontrol:active', function() {
					
					$( this ).removeClass( opts.disabled_class );
				
				} ).on( 'jcarouselcontrol:inactive', function() {
					
					if (
						( !opts.load_more_cb ) || 
						( opts.load_more_cb && oPt.getNoMorePages() )
					) {
						$( this ).addClass( opts.disabled_class );
					}
					
				} ).jcarouselControl( oNextOpts );
				
			};
			
			//// perform initialization
			
			if ( opts.load_more_cb ) {
				
				// load "next" item via ajax
				opts.load_more_cb( eCarWrap, oPt, fCarInit );
				
			} else {
				
				// load carousel outright
				fCarInit();
				
			}
			
			
		} );
		
	};
	
} )( jQuery );
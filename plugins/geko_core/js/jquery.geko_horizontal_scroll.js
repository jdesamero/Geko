;( function ( $ ) {
	
	$.fn.horizontalScroll = function( options ) {

		var opts = $.extend( {
			count: 1,
			proportional_handle: false,
			prev_sel: null,
			next_sel: null,
			inactive_class: 'inactive'
		}, options );
		
		// var bwdt = opts.width;
		// var bhgt = opts.height;
		
		return this.each( function() {
			
			// scrollpane parts
			var curIdx = 0;
			var scrollPane = $( this );
			var scrollContent = scrollPane.find( '.scroll-content' );
			
			var itemWdt = scrollContent.find( '.scroll-content-item' ).width();
			scrollContent.width( itemWdt * opts.count );
			
			
			
			//// state methods
			
			// slide content
			var slideContent = function( slideVal ) {
				if ( scrollContent.width() > scrollPane.width() ) {
					scrollContent.animate( {
						'margin-left': Math.round(
							slideVal / 100 * ( scrollPane.width() - scrollContent.width() )
						) + 'px'
					}, 500 );
				} else {
					scrollContent.animate( { 'margin-left': 0 }, 500 );
				}
				// $( '#scrollvalue span' ).html( slideVal + ' : ' + curIdx );			
			}
			
			// update the prev/next button active/inactive state
			var updatePrevNextState = function() {
				if ( opts.prev_sel ) {
					if ( curIdx == 0 ) $( opts.prev_sel ).addClass( opts.inactive_class );
					else $( opts.prev_sel ).removeClass( opts.inactive_class );
				}
				if ( opts.next_sel ) {
					if ( curIdx == ( opts.count - 1 ) ) $( opts.next_sel ).addClass( opts.inactive_class );
					else $( opts.next_sel ).removeClass( opts.inactive_class );				
				}
			}
			
			
			
			// build slider
			var scrollbar = scrollPane.find( '.scroll-bar' ).slider( {
				step: ( 100 / ( opts.count - 1 ) ),
				slide: function( event, ui ) {
					curIdx = parseInt( ( ui.value / 100 ) * ( opts.count - 1 ) )
					slideContent( ui.value );
					updatePrevNextState();
				}
			} );
			
			if ( opts.proportional_handle ) {
			
				// append icon to handle
				var handleHelper = scrollbar.find(
					'.ui-slider-handle'
				).mousedown( function() {
					scrollbar.width( handleHelper.width() );
				} ).mouseup( function() {
					scrollbar.width( '100%' );
				} ).append(
					'<span class="ui-icon ui-icon-grip-dotted-vertical"><\/span>'
				).wrap(
					'<div class="ui-handle-helper-parent"><\/div>'
				).parent();
				
				// size scrollbar and handle proportionally to scroll distance
				var sizeScrollbar = function() {
					var remainder = scrollContent.width() - scrollPane.width();
					var proportion = remainder / scrollContent.width();
					var handleSize = scrollPane.width() - ( proportion * scrollPane.width() );
					scrollbar.find( '.ui-slider-handle' ).css( {
						width: handleSize,
						'margin-left': -handleSize / 2
					} );
					handleHelper.width( '' ).width( scrollbar.width() - handleSize );
				}
			
			} else {
				
				var sizeScrollbar = function() {};
				
			}
			
			// change overflow to hidden now that slider handles the scrolling
			scrollPane.css( 'overflow', 'hidden' );
						
			// reset slider value based on scroll content position
			var resetValue = function() {
				var remainder = scrollPane.width() - scrollContent.width();
				var leftVal = scrollContent.css( 'margin-left' ) === 'auto' ? 0 :
					parseInt( scrollContent.css( 'margin-left' ) );
				var percentage = Math.round( leftVal / remainder * 100 );
				scrollbar.slider( 'value', percentage );
			}
			
			// if the slider is 100% and window gets larger, reveal content
			var reflowContent = function() {
				var showing = scrollContent.width() + parseInt( scrollContent.css( 'margin-left' ), 10 );
				var gap = scrollPane.width() - showing;
				if ( gap > 0 ) {
					scrollContent.css( 'margin-left', parseInt( scrollContent.css( 'margin-left' ), 10 ) + gap );
				}
			}
						
			if ( opts.prev_sel ) {
				$( opts.prev_sel ).click( function() {
					if ( curIdx > 0 ) {
						curIdx--;
						var val = ( 100 / ( opts.count - 1 ) ) * curIdx;
						scrollbar.slider( 'value', val );
						slideContent( val );
					}
					updatePrevNextState();
					return false;
				} );
			}
			
			if ( opts.next_sel ) {
				$( opts.next_sel ).click( function() {
					if ( curIdx < ( opts.count - 1 ) ) {
						curIdx++;
						var val = ( 100 / ( opts.count - 1 ) ) * curIdx;
						scrollbar.slider( 'value', val );
						slideContent( val );
					}
					updatePrevNextState();
					return false;
				} );
			}
			
			// change handle position on window resize
			$( window ).resize( function() {
				resetValue();
				sizeScrollbar();
				reflowContent();
			} );
			
			// init scrollbar size
			setTimeout( sizeScrollbar, 10 );			//safari wants a timeout
			updatePrevNextState();
			
		} );
	};
	
} )( jQuery );



( function( $ ) {
	
	//// slider
	
	$.fn.gekoSlider = function() {
		
		var mArg1 = arguments[ 0 ];
		
		if ( 'string' == ( typeof mArg1 ) ) {
			
			var eViewport = $( this );
			var eContainer = eViewport.data( 'container' );
			var aOffsets = eViewport.data( 'offsets' );
			var aNamedOffsets = eViewport.data( 'named_offsets' );
			var oAnimate = eViewport.data( 'animate' );
			
			var sOption = mArg1;
			
			if ( 'goto' == sOption ) {
				
				var iPanelIdx = arguments[ 1 ];
				var oAnimOverride = arguments[ 2 ];
				
				if ( !oAnimOverride ) oAnimOverride = {};
				var oAnim = $.extend( {}, oAnimate, oAnimOverride );
				
				var oData = aOffsets[ iPanelIdx ];
				
				if ( oData ) {
					eContainer.animate( { left: oData.offset + 'px' }, oAnim.speed, oAnim.easing, oAnim.callback );
				}

			} else if ( 'gotonamed' == sOption ) {
				
				var iPanelIdx = arguments[ 1 ];
				var oAnimOverride = arguments[ 2 ];
				
				if ( !oAnimOverride ) oAnimOverride = {};
				var oAnim = $.extend( {}, oAnimate, oAnimOverride );
				
				var oData = aNamedOffsets[ iPanelIdx ];
				
				if ( oData ) {
					eContainer.animate( { left: oData.offset + 'px' }, oAnim.speed, oAnim.easing, oAnim.callback );
				}
				
			} else if ( 'debug' == sOption ) {
				
				alert( $.toJSON( aOffsets ) + "\n\n\n" + $.toJSON( aNamedOffsets ) );
				
			}
			
		} else {
			
			var options = mArg1;
			
			var opts = $.extend( {
				set_container_width: true,
				container_sel: '> .container',				// selector for container elem
				panel_sel: '> div',							// selector for panel divs
				named_panel_sel: null,						// selector for "named" panels
				named_panel_sel_cb: null,					// callback function to return collection of "named" panels
				animate: {}
			}, options );
			
			var oAnim = $.extend( {
				speed: 1000,
				easing: 'easeOutQuad',
				callback: $.noop()
			}, opts.animate );
			
			// main
			return this.each( function() {
				
				var aOffsets = [];
				var aNamedOffsets = [];
				var iOffset = 0;
				
				var eViewport = $( this );
				var eContainer = eViewport.find( opts.container_sel );
				var ePanels = eContainer.find( opts.panel_sel );
				
				// go through panels
				ePanels.each( function() {
					
					var ePanel = $( this );
					var iWidth = ePanel.width();
					
					var oData = {
						width: iWidth,
						offset: iOffset * -1,
						named: false
					};
					
					ePanel.data( 'offset', oData );
					aOffsets.push( oData );
					
					iOffset += iWidth;
					
				} );
				
				// set container width
				if ( opts.set_container_width ) {
					eContainer.width( iOffset );
				}
				
				//// go through named panels, if any
				var eNamedPanels = null;
				
				// use a selector
				if ( opts.named_panel_sel ) {
					eNamedPanels = eContainer.find( opts.named_panel_sel );
				}
				
				// use a callback method
				if ( opts.named_panel_sel_cb ) {
					eNamedPanels = opts.named_panel_sel_cb( eContainer );
				}
				
				if ( eNamedPanels ) {
					eNamedPanels.each( function() {
						var eNamedPanel = $( this );
						var oData = eNamedPanel.data( 'offset' );
						oData.named = true;
						aNamedOffsets.push( oData );
					} );
				}
				
				//// track data/params
				
				eViewport.data( 'offsets', aOffsets );
				eViewport.data( 'named_offsets', aNamedOffsets );				
				eViewport.data( 'container', eContainer );
				eViewport.data( 'animate', oAnim );
				
			} );
			
		}
		
	};
	
} )( jQuery );
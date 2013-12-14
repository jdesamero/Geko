;( function ( $ ) {
	
	var reInitCounter = 0;
	
	$.fn.reInit = function( options ) {
		
		if ( 0 == $( '#reinit-items' ).length ) {
			$( 'body' ).append( '<div id="reinit-items" style="display: none;"><\/div>' );
		}
		
		var reInitItems = $( '#reinit-items' );
		
		var opts = $.extend( {
			reinit_target: document,
			reinit_cb: function( widget, evt, param ) {
				return widget;
			}
		}, options );
		
		return this.each( function() {
			
			var origWidget = $( this );
			origWidget.data( 'idx', reInitCounter );								// assign an id
			
			// create a marker to track the location of this node
			var currentWidget = null;
			var marker = $( '<span style="display: none;"><\/span>' );
			origWidget.before( marker );
			
			// move the target element to some hidden place
			origWidget.appendTo( reInitItems );
			origWidget.wrap( '<div id="reinit' + reInitCounter + '" \/>' );			// wrap contents with a unique id
			
			// init clone and position
			$( opts.reinit_target ).bind( opts.reinit_event, function( evt, param ) {
				
				var idx = origWidget.data( 'idx' );
				var widgetClone = reInitItems.find( '#reinit' + idx + ' > *' ).clone();
				
				if ( !currentWidget ) {
					marker.after( widgetClone );
					marker.remove();												// destroy the marker
				} else {
					currentWidget.after( widgetClone );
					currentWidget.remove();											// destroy the old widget
				}
				
				// set the current widget to the clone
				currentWidget = opts.reinit_cb( widgetClone, evt, param );
				
			} );
			
			// init
			reInitCounter++;														// advance counter			
			$( opts.reinit_target ).trigger( opts.reinit_event );					// call first time
			
		} );
	};
	
} )( jQuery );
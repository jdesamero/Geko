;( function ( $ ) {
	
	$.fn.gekoAnimateFrames = function( options ) {
		
		var opts = $.extend( {
			fps: 24,
			width: null,
			height: null
		}, options );
		
		return this.each( function() {
			
			var elem = $( this );
			var width = ( opts.width ) ? opts.width : elem.width();
			var height = ( opts.height ) ? opts.height : elem.height();
			
			var curPos = 0;
			var frames = opts.frames;
			var across = opts.across;
			var down = opts.down;
			var fps = opts.fps;
			
			if ( !frames ) frames = across * down;

			var setBgPos = function() {
				
				var xf = curPos % across;
				var yf = Math.floor( curPos / across );
				var intervalMs = parseInt( 1000 / fps );
				
				elem.css( 'background-position', parseInt( xf * width * -1 ) + 'px ' + parseInt( yf * height * -1 ) + 'px' );
				
				if ( curPos >= ( frames - 1 ) ) {
					
					// last frame
					curPos = 0;
					
					if ( opts.delayRandom ) {
						var min = ( opts.delayRandom.min ) ? opts.delayRandom.min : 0;
						var max = ( opts.delayRandom.max ) ? opts.delayRandom.max : 0;
						randomInterval = $.gekoRandomInt( min, max );
						if ( randomInterval > intervalMs ) intervalMs = randomInterval;
					}
					
				} else {
					curPos++;
				}
				
				setTimeout( setBgPos, intervalMs );
			}
			
			setBgPos();			// init
			
		} );
		
	};
	
	
} )( jQuery );
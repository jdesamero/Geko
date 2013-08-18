// Vertical Scroller Javascript
// copyright 24th September 2005, by Stephen Chapman
// permission to use this Javascript on your web page is granted
// provided that all of the code below (as well as these
// comments) is used without any alteration

// jQuery version by Joel Desamero

;( function ( $ ) {
	
	$.fn.verticalScroller = function( options ) {
		
		var opts = $.extend( {
			speed: 10,			// scroll speed (bigger = faster)
			dR: false,			// reverse direction
			step: 2
		}, options );
		
		
		
		function objWidth( obj ) {
			if ( obj.offsetWidth ) return obj.offsetWidth;
			if ( obj.clip ) return obj.clip.width;
			return 0;
		}
		
		function objHeight( obj ) {
			if ( obj.offsetHeight ) return obj.offsetHeight;
			if ( obj.clip ) return obj.clip.height;
			return 0;
		}
		
		function scrF( i, sH, eH ) {
			var x = parseInt( i.top ) + ( opts.dR ? opts.step: -opts.step );
			if ( opts.dR && x > sH ) x =- eH;
			else if ( x < 2 - eH ) x = sH;
			i.top = x + 'px';
		}
		
		function startScroll( sN, txt ) {
			
			var scr = document.getElementById( sN );
			var sW = objWidth( scr ) - 6;
			var sH = objHeight( scr );
			scr.innerHTML = '<div id="' + sN + 'in" style="position:absolute; left:3px; width:' + sW + ';">' + txt + '<\/div>';
			var sTxt = document.getElementById( sN + 'in' );
			var eH = objHeight( sTxt );
			sTxt.style.top = ( opts.dR ? -eH : sH ) + 'px';
			sTxt.style.clip = 'rect(0,' + sW + 'px,' + eH + 'px,0)';
			
			setInterval(
				function() { scrF( sTxt.style, sH, eH ); },
				1000 / opts.speed
			);
		}
		
		// iterate elements
		return this.each( function() {
			
			var divId = $( this ).attr( 'id' );
			var contents = $( this ).html();
			$( this ).html( '' );
			
			$( window ).load( function() {
				startScroll( divId, contents );			
			} );
			
		} );
		
	};
	
} )( jQuery );



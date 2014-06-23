( function( $ ) {

	$.gekoMapProjection = function( options ) {
		
		var _this = this;
		
		
		var opts = $.extend( {
			
			width: 400,
			height: 200,
			
			type: 'mercator',
			
			xoffset: 0,
			yoffset: 0,
			
			marker_wdt: null,
			marker_hgt: null
			
		}, options );
		
		
		
		
		//// methods
		
		//
		this.getCoords = function( fLat, fLon, oCoordOpts ) {
			
			// vals
			var iXpos = 0;
			var iYpos = 0;
			
			// params
			sType = opts.type;
			iWdt = opts.width;
			iHgt = opts.height;
			
			// convert values to radians
			var fLatRad = $.gekoDegToRad( fLat );
			var fLonRad = $.gekoDegToRad( fLon );			
			
			
			// calculate given map projection
			
			if ( 'cartesian' == sType ) {
				
				iXpos = parseInt( iHgt * Math.cos( fLatRad ) * Math.cos( fLonRad ) );
				iYpos = parseInt( iHgt * Math.cos( fLatRad ) * Math.sin( fLonRad ) );
				
			} else {
				
				// mercator is default
				var fMercN = Math.log( Math.tan( ( Math.PI / 4 ) + ( fLatRad / 2 ) ) );
				
				iXpos = parseInt( ( fLon + 180 ) * ( iWdt / 360 ) );
				iYpos = parseInt( ( iHgt / 2 ) - ( iHgt * fMercN / ( 2 * Math.PI ) ) );
				
			}
			
			// center based on marker width and height
			var iMkWdt = opts.marker_wdt;
			var iMkHgt = opts.marker_hgt;
			
			if ( 'object' == $.type( oCoordOpts ) ) {
				
				if ( oCoordOpts.marker_wdt ) {
					iMkWdt = oCoordOpts.marker_wdt;
				}
				
				if ( oCoordOpts.marker_hgt ) {
					iMkHgt = oCoordOpts.marker_hgt;
				}
				
			}
			
			// apply marker offset
			
			if ( iMkWdt ) iXpos = iXpos - Math.floor( iMkWdt / 2 );
			if ( iMkHgt ) iYpos = iYpos - Math.floor( iMkHgt / 2 );
			
			// apply general offset
			
			if ( opts.xoffset ) iXpos = iXpos + opts.xoffset;
			if ( opts.yoffset ) iYpos = iYpos + opts.yoffset;
			
			//
			return { x: iXpos, y: iYpos };
		};
		
		
	};
	
	
} )( jQuery );
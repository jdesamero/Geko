( function( $ ) {
	
	
	$.fn.gekoSysomosMapfeed = function( options ) {
		
		var opts = $.extend( {
			
			poll_delay: 1000 * 60,
			
			in_effect: 'pulsate',
			in_effect_delay: 1000 / 2,

			out_effect: 'puff',			
			out_effect_delay: 1000 / 2,
			
			visible_delay: 1000 * 10
			
		}, options );
		
		
		//
		return this.each( function() {
			
			var oService = opts.service;
			var oMapProj = opts.map_projection;
			
			var eMap = $( this );
			
			
			var aQueue = [];
			
			
						
			
			var iPollDelay = opts.poll_delay;
			
			var sInEffect = opts.in_effect;
			
			var bHideMarkerFirst = false;
			
			if ( -1 !== $.inArray( sInEffect, [ 'pulsate' ] ) ) {
				bHideMarkerFirst = true;
			}
			
			
			
			var iLastSeq = 0;
			// var bFeedLock = false;
			
			var getMapFeed = function( fDoneCb ) {
				
				
				// TO DO: last sequence id for more accurate feeds
				/* if ( 0 !== iLastSeq ) {
					oMapFeedParams.last_seq = iLastSeq;
				} */
				
				
				oService.get( {
					
					'type': 'map_feed',
					
					'success': function( oRes ) {
				
						if ( oRes.count ) {
							
							var aMapFeed = oRes.map_feed;
							
							$.each( aMapFeed, function( i, v ) {
								
								var fLat = parseFloat( v.latitude );
								var fLon = parseFloat( v.longitude );
								var sLabel = v.location;
								var iSeq = parseInt( v.seq );
								
								var oPos = oMapProj.getCoords( fLat, fLon );
								var iXpos = oPos.x;
								var iYpos = oPos.y;
								
								aQueue.push( {
									label: sLabel,
									x: iXpos,
									y: iYpos
								} );
								
								if ( iSeq > iLastSeq ) {
									iLastSeq = iSeq;
								}
								
								
							} );
							
						}
						
						
						if ( fDoneCb ) fDoneCb();
						
					}
					
				} );
				
				
				
			};
			
			
			//
			var fAddMarker = function() {
				
				var iQueueCount = aQueue.length;
				
				if ( iQueueCount ) {
					
					for ( var i = 0; i < iQueueCount; i++ ) {
						
						setTimeout( function() {

							var oMarker = aQueue.shift();
		
							var eMarker = $( '<div><\/div>' );
							eMarker.addClass( 'marker' );
							eMarker.css( 'left', '%dpx'.printf( oMarker.x ) );
							eMarker.css( 'top', '%dpx'.printf( oMarker.y ) );
							eMarker.attr( 'data-label', oMarker.label );
							
							var eImg = $( '<img />' );
							eImg.attr( 'src', opts.marker_img_url );
							
							eMarker.append( eImg );
							
							if ( bHideMarkerFirst ) {
								eMarker.hide();
							}
							
							eMap.append( eMarker );
							
							eMarker.effect( sInEffect, opts.in_effect_delay, function() {
								setTimeout( function() {
									eMarker.effect( opts.out_effect, opts.out_effect_delay, function() {
										eMarker.remove();
									} );
								}, opts.visible_delay );
							} );
							
						}, $.gekoRandomInt( 0, iPollDelay ) );
											
					}
										
				}
				
				
				getMapFeed();
				
				setTimeout( fAddMarker, iPollDelay );
			};
			
			getMapFeed( fAddMarker );
			
			
		} );
		
	};	

} )( jQuery );


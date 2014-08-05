( function( $ ) {
	
	//// slider
	
	$.fn.gekoMobilymap = function( options ) {
		
		var opts = $.extend( {
			
			onMapLoad: null,
			onDragMap: null,
			mapImgSel: 'img.map',
			
			minimap: null,
			geoproj: null
			
		}, options );
		
		
		//
		var oMmOpts = opts.minimap;
		
		if ( oMmOpts ) {
			
			if ( 'object' !== $.type( oMmOpts ) ) {
				oMmOpts = {};
			}
			
			oMmOpts = $.extend( {
				mainSel: '.minimap',
				viewerSel: '.viewer'				
			}, oMmOpts );
			
		}
		
		
		//
		var oGeoOpts = opts.geoproj;
		
		if ( oGeoOpts ) {
			
			if ( 'object' !== $.type( oGeoOpts ) ) {
				oGeoOpts = {};
			}
			
			oGeoOpts = $.extend( {
				type: 'mercator'
			}, oGeoOpts );
			
		}
		
		
		// console.log( oMmOpts );
		
		
		// helpers 
		var fnReposViewer = function( eViewer, iLeft, iTop, xRFactor, yRFactor ) {
			eViewer.css( {
				left: '%dpx'.printf( ( iLeft * -1 ) * xRFactor ),
				top: '%dpx'.printf( ( iTop * -1 ) * yRFactor )
			} );
		};
		
		
		// main
		return this.each( function() {
			
			var eMap = $( this );
			var eMapImg = eMap.find( opts.mapImgSel );
			
			if ( oMmOpts ) {
				var eMiniMap = eMap.parent().find( oMmOpts.mainSel );
				var eViewer = eMiniMap.find( oMmOpts.viewerSel );
				eViewer.hide();
			}
			
			
			//// process markers
			
			if ( oGeoOpts && $.gekoMapProjection ) {
				
				// map position
				
				var iCenterLat = null;
				if ( oGeoOpts.center_lat ) {
					iCenterLat = oGeoOpts.center_lat;
					delete oGeoOpts.center_lat;
				}

				var iCenterLng = null;
				if ( oGeoOpts.center_lng ) {
					iCenterLng = oGeoOpts.center_lng;				
					delete oGeoOpts.center_lng;
				}
				
				// set up projection
				var oMapProj = new $.gekoMapProjection( oGeoOpts );
				
				
				// calculate center
				if ( ( !opts.position ) && iCenterLat && iCenterLng ) {
					
					var oCenPos = oMapProj.getCoords( iCenterLat, iCenterLng );
					
					var iCenXpos = parseInt( oCenPos.x - ( eMap.width() / 2 ) );
					var iCenYpos = parseInt( oCenPos.y - ( eMap.height() / 2 ) );
					
					var iMapWdt = eMapImg.width();
					var iMapHgt = eMapImg.height();
					
					if ( iCenXpos < 0 ) iCenXpos = 0;
					else if ( iCenXpos > iMapWdt ) iCenXpos = iMapWdt;
					
					if ( iCenYpos < 0 ) iCenYpos = 0;
					else if ( iCenYpos > iMapHgt ) iCenYpos = iMapHgt;
					
					opts.position = '%d %d'.printf( iCenXpos, iCenYpos );
				}
				
				// pointers
				
				eMap.find( '.point' ).each( function() {
					
					var eMarker = $( this );
					
					var sCoords = eMarker.attr( 'data-coords' );
					
					if ( !eMarker.attr( 'id' ) && sCoords ) {
						
						var aCoords = sCoords.split( ',' );

						var oPos = oMapProj.getCoords( parseFloat( aCoords[ 0 ] ), parseFloat( aCoords[ 1 ] ) );
						var iXpos = parseInt( oPos.x + aCoords[ 2 ] );
						var iYpos = parseInt( oPos.y + aCoords[ 3 ] );
						
						eMarker.attr( 'id', 'p-%d-%d'.printf( iXpos, iYpos ) );
					}
					
				} );
				
			}
			
			
			//// function to get called with onMapLoad
			var fnGekoMapLoad =  null;
			
						
			//
			if ( opts.onDragMap || oMmOpts ) {
				
				//
				fnGekoMapLoad = function() {
					
					//
					var eImageContent = eMap.find( '.imgContent' );
										
					//
					if ( oMmOpts ) {
						
						
						
						var iMapWdt = eMap.width();
						var iMapHgt = eMap.height();
						
						var iViewWdt = eViewer.width();
						var iViewHgt = eViewer.height();
						
						
						
						// width and height of map window divided by viewer to get ratio
						// for dragging viewer
						var xFactor = iMapWdt / iViewWdt;
						var yFactor = iMapHgt / iViewHgt;
						
						var xRFactor = iViewWdt / iMapWdt;
						var yRFactor = iViewHgt / iMapHgt;

						
						
						// console.log( eImageContent.position() );
						
						// console.log( oMmOpts.mainSel );
						// console.log( eMiniMap.length );
						
						// makes viewer draggable and moves map
						eViewer.draggable( {
							
							containment: eMiniMap,
							drag: function( event, ui ) {
							
								var newX = parseInt( ui.position.left * xFactor ); // creates ratio relationship between viewer and map window
								var newY = parseInt( ui.position.top * yFactor );
								
								// console.log( ui.position );
								// console.log( newX + ' : ' + newY );
								
								//sets map image left and top according to newX and newY
								eImageContent.css( {
									left: '-%dpx'.printf( newX ),
									top: '-%dpx'.printf( newY )
								} );
							}
							
						} );
						
						
						// init viewer state
						var pos = eImageContent.position();
						fnReposViewer( eViewer, pos.left, pos.top, xRFactor, yRFactor );
						eViewer.show();
						
					}
					
					
					// this stuff has to happen regardless
					var isDragging = false;
					
					// moves viewer when map is dragged
					eImageContent.on( 'mousedown', function() {

						isDragging = true;
					
					} ).on( 'mouseup', function() {

						isDragging = false;
					
					} ).on('mousemove', function( e ) {
						
						if ( isDragging ) {
							
							if ( oMmOpts ) {
																
								// console.log( e.pageX + ' : ' + e.pageY );
								// console.log( eImageContent.position() );
								
								var pos = eImageContent.position();
								fnReposViewer( eViewer, pos.left, pos.top, xRFactor, yRFactor );
								
							}
							
							if ( opts.onDragMap ) {
								opts.onDragMap.call( eMap, e );
							}
							
						}
						
					} );
					
					
				}
				
			}
			
			//// invoke onMapLoad
			
			if ( fnGekoMapLoad ) {
				
				if ( opts.onMapLoad ) {
					
					var fnOnMapLoadOrig = opts.onMapLoad;
					
					opts.onMapLoad = function() {
						
						fnOnMapLoadOrig.call( $( this ) );
						fnGekoMapLoad.call( $( this ) );
						
					};
					
				} else {
					
					opts.onMapLoad = fnGekoMapLoad;
					
				}
			
			}
			
			
			eMap.mobilymap( opts );
			
			
		} );
		
	};
	
} )( jQuery );
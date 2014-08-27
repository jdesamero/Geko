( function( $ ) {
	
	//// slider
	
	$.fn.gekoMobilymap = function( options ) {
		
		var opts = $.extend( {
			
			onMapLoad: null,
			onDragMap: null,
			mapImgSel: 'img.map',
			
			minimap: null,
			geoproj: null,
			movemap: null,
			
			viewPortWidth: null,
			viewPortHeight: null,
			
		}, options );
		
		
		
		//// minimap opts
		
		var oMmOpts = opts.minimap;
		
		if ( oMmOpts ) {
			
			if ( 'object' !== $.type( oMmOpts ) ) {
				oMmOpts = {};
			}
			
			oMmOpts = $.extend( {
				mainSel: '.minimap',
				viewerSel: '.viewer',
				markerCallback: null
			}, oMmOpts );
			
		}
		
		
		//// geo-projection opts
		
		var oGeoOpts = opts.geoproj;
		
		if ( oGeoOpts ) {
			
			if ( 'object' !== $.type( oGeoOpts ) ) {
				oGeoOpts = {};
			}
			
			oGeoOpts = $.extend( {
				type: 'mercator'
			}, oGeoOpts );
			
		}
		
		
		//// move map opts (aka map animation)
		
		var oMoveOpts = opts.movemap;
		
		if ( oMoveOpts ) {
			
			if ( 'object' !== $.type( oMoveOpts ) ) {
				oMoveOpts = {};
			}
			
			oMoveOpts = $.extend( {
				restartDelay: 5000
			}, oMoveOpts );
		}
		
		
		
		//// helpers
		
		//
		var mapCheck = function( x, y, eMap, eImageContent ) {
			
			if ( y < ( eMap.height() - eImageContent.height() ) ) {
				y = eMap.height() - eImageContent.height();
			} else {
				if( y > 0 ) y = 0;
			}
			
			if ( x < ( eMap.width() - eImageContent.width() ) ) {
				x = eMap.width() - eImageContent.width();
			} else {
				if ( x > 0 ) x = 0;
			}
			
			return { x:x, y:y };
		};
		
		
		// main
		return this.each( function() {
			
			var eMap = $( this );
			
			eMap.gekoObserver( {
				prefix: 'map',
				events: 'reposition'
			} );
						
			
			var eMapImg = eMap.find( opts.mapImgSel );
			
			var iScaleFactor = null;
			var sMiniMarkerClass = null;
			
			if ( oMmOpts ) {
				
				var eMiniMap = eMap.parent().find( oMmOpts.mainSel );
				var eViewer = eMiniMap.find( oMmOpts.viewerSel );
				
				eMap.gekoObserver( 'register', eViewer );
				
				
				eViewer.on( 'map:reposition', function( e, iLeft, iTop, xRFactor, yRFactor ) {
					$( this ).css( {
						left: '%dpx'.printf( ( iLeft * -1 ) * xRFactor ),
						top: '%dpx'.printf( ( iTop * -1 ) * yRFactor )
					} );
				} );
				
				eViewer.hide();
				
				if ( oMmOpts.dynamicViewerScale ) {
					iScaleFactor = oMmOpts.dynamicViewerScale;
				}

				if ( oMmOpts.markerClass ) {
					sMiniMarkerClass = oMmOpts.markerClass;
				}
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
					
					var iMapWinWdt = opts.viewPortWidth;
					if ( !iMapWinWdt ) iMapWinWdt = eMap.width();
					
					var iMapWinHgt = opts.viewPortHeight;
					if ( !iMapWinHgt ) iMapWinHgt = eMap.height();
					
					var iCenXpos = parseInt( oCenPos.x - ( iMapWinWdt / 2 ) );
					var iCenYpos = parseInt( oCenPos.y - ( iMapWinHgt / 2 ) );
					
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
						var iXpos = parseInt( oPos.x + parseInt( aCoords[ 2 ] ) );
						var iYpos = parseInt( oPos.y + parseInt( aCoords[ 3 ] ) );
						
						eMarker.attr( 'id', 'p-%d-%d'.printf( iXpos, iYpos ) );
					}
					
					
					if ( iScaleFactor && sMiniMarkerClass ) {
						
						var aMmPoint = eMarker.attr( 'id' ).split( '-' );

						var eMiniMarker = $( '<div></div>' );
						
						eMiniMarker.addClass( sMiniMarkerClass );
						
						eMiniMarker.css( {
							left: '%dpx'.printf( parseInt( aMmPoint[ 1 ] ) / iScaleFactor ),
							top: '%dpx'.printf( parseInt( aMmPoint[ 2 ] ) / iScaleFactor )
						} );
						
						eMiniMap.prepend( eMiniMarker );
						
						if ( oMmOpts.markerCallback ) {
							oMmOpts.markerCallback.call( eMap, eMiniMarker, eMarker );
						}
					}
											
				} );
				
			}
			
			
			//// function to get called with onMapLoad
			var fnGekoMapLoad =  null;
			
						
			//
			if ( opts.onDragMap || oMmOpts || oMoveOpts ) {
				
				//
				fnGekoMapLoad = function() {
					
					//
					var eImageContent = eMap.find( '.imgContent' );
					
					var mapTimeout, restartTimeout;
					var mapLock = false;						
					
					var leftOffset = -1;
					var topOffset = 1;
					
					var fnStopMap, fnStopMapDelay;
					
					
					//// map animation stuff
					
					if ( oMoveOpts ) {
						
						eMap.on( 'move', function() {
							
							mapLock = true;
							
							var pos = eImageContent.position();
							
							var bottom = -( eImageContent.height() - eMap.height() );
							var right = -( eImageContent.width() - eMap.width() );
							
							if ( pos.top == 0 ) {
								topOffset = -1;
							} else if ( pos.top == bottom ) {
								topOffset = 1;
							}
							
							if ( pos.left == 0 ) {
								leftOffset = -1;
							} else if ( pos.left == right ) {
								leftOffset = 1;
							}
							
							var checkPos = mapCheck( pos.left + leftOffset, pos.top + topOffset, eMap, eImageContent );
													
							eImageContent.css( { 'left': checkPos.x + 'px', 'top': checkPos.y + 'px' } );
							
							
							eMap.trigger( 'reposition', [ pos.left, pos.top, xRFactor, yRFactor ] );
							
							mapTimeout = setTimeout( function() {
								eMap.trigger( 'move' );
							}, 30 );
							
						} );
						
						eMap.on( 'stop', function( e, delay ) {
							
							var fnMoveMap = function() {
								eMap.trigger( 'move' );					
							};
							
							if ( mapLock ) {
								
								clearTimeout( mapTimeout );
								mapLock = false;
								
								if ( delay ) {
									restartTimeout = setTimeout( fnMoveMap, delay );
								}
								
							} else {
								
								clearTimeout( restartTimeout );
								
								if ( delay ) {
									restartTimeout = setTimeout( fnMoveMap, delay );
								}
								
							}
							
						} );
						
						
						// $( '.imgContent, #viewer' )
						
						fnStopMap = function() {
							eMap.trigger( 'stop' );
						};
						
						fnStopMapDelay = function() {
							eMap.trigger( 'stop', [ oMoveOpts.restartDelay ] );
						};
						
						eImageContent
							.on( 'mousedown', fnStopMap )
							.on( 'mouseup', fnStopMapDelay )
						;
						
					}
					
					
					
					//
					if ( oMmOpts ) {
						
						if ( iScaleFactor ) {
							
							var fnResize = function(){
								
								var map = eMap.data( 'mmap' );
								var pos = eImageContent.position();
								//console.log( pos );
											
								var checkPos = mapCheck( pos.left, pos.top, eMap, eImageContent );
													
								eImageContent.css( { 'left': checkPos.x + 'px', 'top': checkPos.y + 'px' } );
								
								eViewer
									.css( 'width', eMap.width() / iScaleFactor )
									.css( 'height', eMap.height() / iScaleFactor )
								;
								
							};
							
							$( window ).resize( fnResize );
							
							// init
							fnResize();
						}
						
						
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
						
						eMap.trigger( 'reposition', [ pos.left, pos.top, xRFactor, yRFactor ] );
						
						eViewer.show();
						
						
						if ( fnStopMap ) {
							
							eViewer
								.on( 'mousedown', fnStopMap )
								.on( 'mouseup', fnStopMapDelay )
							;
						}
						
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
								//fnReposViewer( eViewer, pos.left, pos.top, xRFactor, yRFactor );
								
								eMap.trigger( 'reposition', [ pos.left, pos.top, xRFactor, yRFactor ] );
								
							}
							
							if ( opts.onDragMap ) {
								opts.onDragMap.call( eMap, e );
							}
							
						}
						
					} );
						
					
					
					// animate map
					if ( oMoveOpts ) {
						eMap.trigger( 'move' );
					}
					
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
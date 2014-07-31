( function( $ ) {
	
	//// slider
	
	$.fn.gekoMobilymap = function( options ) {
		
		var opts = $.extend( {
			
			onMapLoad: null,
			onDragMap: null,
			
			minimap: null
			
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
		
		
		// console.log( oMmOpts );
		
		
		// helpers 
		var fnReposViewer = function( eViewer, iLeft, iTop, xRFactor, yRFactor ) {
			eViewer.css( {
				left: parseInt( ( iLeft * -1 ) * xRFactor ) + 'px',
				top: parseInt( ( iTop * -1 ) * yRFactor ) + 'px'
			} );
		};
		
		
		// main
		return this.each( function() {
			
			var eMap = $( this );
			
			if ( oMmOpts ) {
				var eMiniMap = eMap.parent().find( oMmOpts.mainSel );
				var eViewer = eMiniMap.find( oMmOpts.viewerSel );
				eViewer.hide();
			}
			
			// function to get called with onMapLoad
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
									left: '-' + ( newX ) + 'px',
									top: '-' + ( newY ) + 'px'
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
// http://www.jqueryscript.net/slider/Creating-3D-Perspective-Carousel-with-jQuery-CSS3-CSSSlider.html
// http://www.jqueryscript.net/demo/Creating-3D-Perspective-Carousel-with-jQuery-CSS3-CSSSlider/

;( function ( $ ) {
	
	var iGroup = 0;
	
	$.fn.gekoConclave = function( options ) {
		
		
		iGroup++;			// important, only advance on init
		
		var opts = $.extend( {
			// ...
		}, options );
		
		
		
		
		
		var cRound = function( fVal ) {
			return ( fVal * 100 ).toFixed( 2 );
		};
		
		// perform calculations and return values to be used by CSS
		var cCalculate = function( oParams ) {
			
			var fWidth = oParams.width;
			var fHeight = oParams.height;
			var fLeftOffset = oParams.leftOffset;						// it is assumed the right offset is the same as the left
			
			var aPanels = oParams.panels;
			
			var aRet = [];
			var fRunningTopOffset = 0;
			var fRunningLeftOffset = fLeftOffset / fWidth;
			
			// round 1, calculate the panels
			
			$.each( aPanels, function( i, v ) {
				
				fRunningTopOffset += v.topOffset;
				
				var oVals = {
					widthPct: v.width / fWidth,							// we'll round this later
					heightPct: cRound( v.height / fHeight ),
					topOffsetPct: cRound( fRunningTopOffset / fHeight )
				};
				
				if ( 0 == i ) {
					
					oVals.isCenter = true;
					aRet.push( oVals );
					
				} else {
					
					// prepend and append
					aRet.unshift( oVals );
					aRet.push( $.extend( true, {}, oVals ) );			// clone!		
					
				}
				
			} );
			
			
			// round 2, calculate the left offsets
			$.each( aRet, function( i, v ) {
				
				v.leftOffsetPct = cRound( fRunningLeftOffset );
				
				fRunningLeftOffset += v.widthPct;
				
				v.widthPct = cRound( v.widthPct );						// now rounded
				
			} );
			
			
			return aRet;
		};
		
		
		var aCalcPanels = cCalculate( opts );
		var arlen = aCalcPanels.length;
		
		
		// console.log( aCalcPanels );
		
		
		//// create an internal stylesheet
		
		
		var sStyleSheet = "<style>\n\n";
		
		var sHolderPattern = 'holder%d_p%%d'.printf( iGroup );
		
		$.each( aCalcPanels, function( i, v ) {
			
			var sHolderClass = sHolderPattern.printf( i );
			
			sStyleSheet += ".%s {\n".printf( sHolderClass );
			sStyleSheet += "    top: %s%%;\n".printf( v.topOffsetPct );
			sStyleSheet += "    left: %s%%;\n".printf( v.leftOffsetPct );
			sStyleSheet += "    width: %s%%;\n".printf( v.widthPct );
			sStyleSheet += "    height: %s%%;\n".printf( v.heightPct );
			sStyleSheet += "}\n\n";
						
		} );
		
		var iCenterPos = Math.floor( arlen / 2 );
		
		var sDurPattern = 'holder%d_dr%%d'.printf( iGroup );
		
		for ( var i = 1; i <= iCenterPos; i++ ) {
			
			var fDur = 1 / i;
			var sDurClass = sDurPattern.printf( i );
			
			/* /
			sStyleSheet += ".%s {\n".printf( sDurClass );
			sStyleSheet += "    -webkit-transition: width %ss, height %ss, top %ss, left %ss;\n".printf( fDur, fDur, fDur, fDur );
			sStyleSheet += "    -moz-transition: width %ss, height %ss, top %ss, left %ss;\n".printf( fDur, fDur, fDur, fDur );
			sStyleSheet += "    -o-transition: width %ss, height %ss, top %ss, left %ss;\n".printf( fDur, fDur, fDur, fDur );
			sStyleSheet += "    -ms-transition: width %ss, height %ss, top %ss, left %ss;\n".printf( fDur, fDur, fDur, fDur );
			sStyleSheet += "    transition: width %ss, height %ss, top %ss, left %ss\n".printf( fDur, fDur, fDur, fDur );
			sStyleSheet += "}\n\n";
			/* */
			
		}
		
		
		sStyleSheet += "\n\n</style>";
		
		$( 'head' ).append( sStyleSheet );
		
		
		
		
		//
		return $( this ).each( function() {
			
			var eWrapper = $( this );
			
			
			
			var aPos = [];
			var cMoveIt;
			
			
			// init
			eWrapper.find( 'div.holder_bu' ).each( function( i ) {
				
				var eDiv = $( this );
				
				eDiv.on(
					'webkitTransitionEnd otransitionend oTransitionEnd msTransitionEnd transitionend',   
					function( e ) {
						
						eDiv.attr( 'data-intrans', 0 );
						
						// only proceed with next move if no more panels are in transition
						if ( 0 == eWrapper.find( 'div[data-intrans="1"]' ).length ) {
							cMoveIt();
						}
						
					}
				);

				
				var sClass = sHolderPattern.printf( i );
				
				eDiv
					.addClass( sClass )
					.attr( 'data-buid', i )
					.attr( 'data-bupos', i )				// initial state
				;
				
				aPos.push( i );
								
			} );
			
			
			
			eWrapper.find( '.holder_bu' ).on( 'click', function() {
				
				var eClickPanel = $( this );
								
				var iClickPos = parseInt( eClickPanel.attr( 'data-bupos' ) );
				
				// check if we're going left or right
				var iDirCheck = iCenterPos - iClickPos;				
				
				
				
				if ( iDirCheck ) {
					
					// move algorithm
					var iSteps = Math.abs( iDirCheck );
					var iCount = iSteps;
					
					cMoveIt = function() {
						
						if ( iCount ) {
						
							var t, sSel, sClass, ePanel, sZindexClass, sDurClass;
							
							eWrapper.find( '.holder_bu' ).css( 'z-index', 100 );
							
							if ( iDirCheck > 0 ) {
								
								t = aPos.shift();
								aPos.push( t );
								
								sZindexClass = 'div[data-bupos="%d"]'.printf( arlen - 1 );
								
							} else {
							
								t = aPos.pop();
								aPos.unshift( t );
								
								sZindexClass = 'div[data-bupos="0"]';
							}
							
							var eBottomDiv = eWrapper.find( sZindexClass );
							
							eBottomDiv.css( 'z-index', 90 );
							
							
							for ( var i = 0; i < arlen; i++ ) {
								
								sSel = 'div[data-buid="%d"]'.printf( i );
								
								sClass = sHolderPattern.printf( aPos[ i ] );
								sDurClass = sDurPattern.printf( iSteps );
								
								// console.log( sDurClass );
								
								ePanel = eWrapper.find( sSel );
								
								ePanel
									.removeClass()									// reset
									.addClass( sClass )
									.addClass( sDurClass )
									.addClass( 'holder_bu' )
									.attr( 'data-bupos', aPos[ i ] )
									.attr( 'data-intrans', 1 )						// mark all panels as "in transition"
								;
								
							}
							
							eBottomDiv.data( 'moveIt', true );
							
							iCount--;
							
						}
						
					};
					
					cMoveIt();
					
				}
				
			} );
			
			
		} );
	};

} )( jQuery );


/* /
auto: function() {
	
	eWrapper.find( '.holder_bu' ).delay( 4000 ).trigger( 'click' ).delay( 4000 );
	console.log( 'called' );
	
}
/* */



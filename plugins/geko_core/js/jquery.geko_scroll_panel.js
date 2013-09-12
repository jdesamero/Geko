;( function ( $ ) {
	
	$.fn.gekoScrollPanel = function() {
		
		var firstArg = arguments[ 0 ];
		
		if ( 'string' == typeof firstArg ) {
			
			if ( 'scrollTo' == firstArg ) {
				
				var target = arguments[ 1 ];
				var delay = arguments[ 2 ];
				var easingFunc = arguments[ 3 ];
				
				if ( !delay ) delay = 1000;
				if ( !easingFunc ) easingFunc = 'easeInOutSine';
				
				$.fn.scrollPath( 'scrollTo', target, delay, easingFunc );
				
				return $( this );
				
			} else if ( 'curStep' == firstArg ) {
				
				return $( this ).data( 'curStep' );
				
			} else if ( 'range' == firstArg ) {

				var target = arguments[ 1 ];
				
				var gp = $.fn.scrollPath( 'getPath' );
				var stepMap = gp.getStepMap();
				
				return stepMap[ target ];
			}
			
		} else {
			
			var opts = $.extend( {
				drawPath: true,
				wrapAround: true,
				scrollBar: true,
				panels: {},
				trackShift: {
					'far': 200,
					'mid': 500,
					'near': 900
				},
				panelSel: '.panel',
				overlayPanelSel: '#overlay-panel',
				navigationSel: '#navigation',
				stepCallback: function( step ) { },
				curPanelPosCallback: function( i, id ) { },
				debug: false
			}, firstArg );
			
			
			
			return this.each( function() {
				
				var elem = $( this );
				
				elem.data( 'curStep', 0 );
				
				var panels = elem.find( opts.panelSel );
				var overlayPanel = $( opts.overlayPanelSel );
				
				
				
				//// scroll path
				
				var gp = $.fn.scrollPath( 'getPath' );
	
				var x = 0;
				var y = 0;
				
				gp.moveTo( x, y );
	
				
				
				// down arrow key fix for firefox
				if ( $.browser.mozilla ) {
					var htmlNode = $( 'html' ).get( 0 );
					$( window ).scroll( function() {
						htmlNode.scrollTop = 0;
					} );
				}
				
				// disable css3 rotation on anything below IE8
				var browserVersion = parseInt( $.browser.version, 10 );
				var cssRotationSupported = (
					( !$.browser.msie ) || 
					( ( $.browser.msie ) && ( browserVersion > 8 ) )
				) ? true : false ;
				
				
				
				// track the primary class
				panels.each( function() {
					var panel = $( this );
					var dummy = $( '<div><\/div>' );
					dummy.attr( 'class', panel.attr( 'class' ) );
					dummy.removeClass( 'panel' );
					panel.data( 'id', $.trim( dummy.attr( 'class' ) ) );
					dummy.remove();
				} );
				
				
				
				var spriteHash = {};
				
				var nextOffsetX = 0;
				var nextOffsetY = 0;
				
				$.each( opts.panels, function( k, v ) {
					
					// create sprite hash
					if ( v.sprite ) {
						spriteHash[ k ] = v.sprite;
						$.each( v.sprite, function( j, w ) {
							var sa = elem.find( '.' + k + ' .' + j );
							if ( sa.length && w.animateFrames ) {
								sa.gekoAnimateFrames( w.animateFrames );
							}
						} );
					}
					
					// setup scroll path
					
					x = x + nextOffsetX;
					y = y + nextOffsetY;
					
					var gpOpts = { name: k };
					
					// pin panel to the center of x, y
					var panel = elem.find( '.' + k );
					var pwd = panel.width();
					var phg = panel.height();
					
					panel.css( 'left', parseInt( x - ( pwd / 2 ) ) + 'px' );
					panel.css( 'top', parseInt( y - ( phg / 2 ) ) + 'px' );
					
					gp.lineTo( x, y, gpOpts );
					
					nextOffsetX = ( v.xoffset ) ? v.xoffset : 0 ;
					nextOffsetY = ( v.yoffset ) ? v.yoffset : 0 ;
					
				} );
				
				
				var overlaySpriteHash = {};
				$.each( opts.panels, function( k, v ) {
					overlaySpriteHash[ k ] = v.overlaySprite;
				} );
				
				
				
				var fPathCallback = function( step, pathObject, pathList ) {
					
					// helpers
					var getPxValue = function( elem, cssProp ) {
						var value = elem.css( cssProp );
						if ( value ) {
							return parseInt( value.replace( 'px', '' ) );
						}
						return null;
					}
					
					var getLeftPx = function( left, right, wdt, panelWidth ) {
						if ( ( null !== left ) && ( undefined !== left ) ) return left;
						if ( ( null !== right ) && ( undefined !== right ) ) {
							return parseInt( panelWidth - wdt - right );
						}
						return 0;
					}
					
					var getBottomPx = function( bottom, top, hgt, panelHeight ) {
						if ( ( null !== bottom ) && ( undefined !== bottom ) ) return bottom;
						if ( ( null !== top ) && ( undefined !== top ) ) {
							return parseInt( panelHeight - hgt - top );
						}
						return 0;
					}
					
					var stepMap = pathObject.getStepMap();
					
					var numPanels = panels.length;
					panels.each( function( i ) {
						
						var panel = $( this );
						
						var panelHeight = panel.height();
						var panelWidth = panel.width();
						var panelRatio = panelHeight / panelWidth;
						
						var id = panel.data( 'id' );				
						var range = stepMap[ id ];
						
						if ( range ) {
							
							var curX = pathList[ step ].x;
							var curY = pathList[ step ].y;
							
							var startX = pathList[ range.startStep ].x;
							var startY = pathList[ range.startStep ].y;
							
							var startStep = range.startStep;
							
							var endStep, endX, endY;
							if ( range.endStep ) {
								endStep = range.endStep;
								endX = pathList[ range.endStep ].x;
								endY = pathList[ range.endStep ].y;
							} else {
								endX = startX + nextOffsetX;
								endY = startY + nextOffsetY;
								endStep = startStep + ( Math.sqrt( Math.pow( ( startX - endX ), 2 ) + Math.pow( ( startY - endY ), 2 ) ) );
							}
							
							var pnlOffStep = endStep - startStep;
							var pnlOffX = endX - startX;
							var pnlOffY = endY - startY;
							
							var midStep = parseInt( startStep + ( pnlOffStep / 2 ) );
							var midX = parseInt( startX + ( pnlOffX / 2 ) );
							var midY = parseInt( startY + ( pnlOffY / 2 ) );
							
							var posStep = ( ( midStep - step - ( pnlOffStep / 2 ) ) * -1 );
							var posX = ( ( midX - curX - ( pnlOffX / 2 ) ) * -1 );
							var posY = ( ( midY - curY - ( pnlOffY / 2 ) ) * -1 );
							
							var pct = ( 0 == pnlOffStep ) ? 0 : ( posStep / pnlOffStep );
							var pctX = ( 0 == pnlOffX ) ? 0 : ( posX / pnlOffX );
							var pctY = ( 0 == pnlOffY ) ? 0 : ( posY / pnlOffY );
							
							if ( i == ( panels.length - 1 ) ) {
								if ( pct > 0 ) pct = 0;
								if ( pctX > 0 ) pctX = 0;
								if ( pctY > 0 ) pctY = 0;
							}
							
							var bCurrent = ( ( step >= startStep ) && ( step < endStep ) ) ? true : false ;
							
							// highlight nav
							if ( bCurrent ) opts.curPanelPosCallback( i, id );
							
							//
							if ( opts.debug ) {
								
								var debugInfo = 'id: ' + id + 
									', step: ' + step + 
									', curX/curY: ' + curX + '/' + curY +
									', startStep: ' + startStep + 
									', startX/startY: ' + startX + '/' + startY +
									',<br \/>endStep: ' + endStep + 
									', endX/endY: ' + endX + '/' + endY +
									', midStep: ' + midStep + 							
									', midX/midY: ' + midX + '/' + midY + 
									',<br \/>pnlOffStep: ' + pnlOffStep + 
									', pnlOffX/pnlOffY: ' + pnlOffX + '/' + pnlOffY + 
									', posX/posY: ' + posX + '/' + posY +
									', pct: ' + parseInt( pct * 100 ) + 
									', pctX/pctY:' + parseInt( pctX * 100 ) + '/' + parseInt( pctY * 100 )
								;
								
								panel.find( 'span.info' ).html( debugInfo );
								if ( 'colour-premium' == id ) $( '#info_div' ).html( debugInfo );
							}
							
							//
							panel.find( '.track' ).each( function() {
								
								var track = $( this );
								
								var shiftX = 0, shiftY = 0;
								$.each( opts.trackShift, function( k, v ) {
									if ( track.hasClass( k ) ) {
										shiftX = v;
										return false;
									}
								} );
								
								shiftY = parseInt( shiftX * panelRatio );
								
								// background fade animation
								if ( track.hasClass( 'bg' ) ) {
									if ( pct < -1 ) track.css( 'opacity', 1 );
									else if ( pct >= 0 ) track.css( 'opacity', 0 );
									else track.css( 'opacity', ( pct * -1 ) );
								}
								
								if ( track.hasClass( 'bg2' ) ) {
									if ( pct < 0 ) track.css( 'opacity', 0 );
									else if ( pct >= 1 ) track.css( 'opacity', 1 );
									else track.css( 'opacity', pct );
								}
								
								var offsetX = parseInt( shiftX * pctX );
								var offsetY = parseInt( shiftY * pctY );
								
								track.css( 'left', offsetX + 'px' );
								track.css( 'top', offsetY + 'px' );
								
								// track sprites
								var sprites = spriteHash[ id ];
								if ( sprites ) {
									$.each( sprites, function( k, v ) {
										
										var sprite = track.find( '.' + k );
										
										if ( sprite.length > 0 ) {
											
											if ( v.enter ) {
											
												var xo = v.enter.xoffset;
												var yo = v.enter.yoffset;
												
												if ( !sprite.data( 'curpos' ) ) {
													
													var top = getPxValue( sprite, 'top' );
													var right = getPxValue( sprite, 'right' );
													var bottom = getPxValue( sprite, 'bottom' );
													var left = getPxValue( sprite, 'left' );
													
													sprite.data( 'curpos', {
														top: top,
														right: right,
														bottom: bottom,
														left: left
													} );
													
												}
												
												var cp = sprite.data( 'curpos' );
												
												if ( pct < -1 ) {
													
													if ( null !== cp.left ) sprite.css( 'left', ( cp.left + xo ) + 'px' );
													if ( null !== cp.right ) sprite.css( 'right', ( cp.right - yo ) + 'px' );
													
													if ( null !== cp.top ) sprite.css( 'top', ( cp.top + yo ) - 'px' );
													if ( null !== cp.bottom ) sprite.css( 'bottom', ( cp.bottom + yo ) + 'px' );
													
												} else if ( pct >= 0 ) {
													
													if ( null !== cp.left ) sprite.css( 'left', cp.left + 'px' );
													if ( null !== cp.right ) sprite.css( 'right', cp.right + 'px' );
													
													if ( null !== cp.top ) sprite.css( 'top', cp.top + 'px' );
													if ( null !== cp.bottom ) sprite.css( 'bottom', cp.bottom + 'px' );
													
												} else {
													
													if ( null !== cp.left ) sprite.css( 'left', ( cp.left + parseInt( xo * ( pct * -1 ) ) ) + 'px' );
													if ( null !== cp.right ) sprite.css( 'right', ( cp.right - parseInt( xo * ( pct * -1 ) ) ) + 'px' );
													
													if ( null !== cp.top ) sprite.css( 'top', ( cp.top - parseInt( yo * ( pct * -1 ) ) ) + 'px' );
													if ( null !== cp.bottom ) sprite.css( 'bottom', ( cp.bottom + parseInt( yo * ( pct * -1 ) ) ) + 'px' );
													
													/* if ( 'clip' == k ) {
														panel.find( 'span.info' ).html( 'left: ' + sprite.css( 'left' ) + ', bottom: ' + sprite.css( 'bottom' ) );									
													} */
													
												}
											}
											
											if ( v.fade ) {
												if ( v.fade[ 'in' ] ) {
													if ( pct < -1 ) sprite.css( 'opacity', 0 );
													else if ( pct >= -1 && pct < 0 ) sprite.css( 'opacity', 1 + pct );
													else sprite.css( 'opacity', 1 );
												}
												if ( v.fade.out ) {
													if ( pct >= 0 && pct < 1 ) sprite.css( 'opacity', ( 1 - pct ) );
													else if ( pct >= 1 ) sprite.css( 'opacity', 0 );
												}
											}
											
											// TO DO: implement wipe effect
											if ( v.wipe ) {
												if ( v.wipe[ 'in' ] ) {
													if ( 'top' == v.wipe[ 'in' ] ) {
													
													} else if ( 'right' == v.wipe[ 'in' ] ) {
													
													} else if ( 'bottom' == v.wipe[ 'in' ] ) {
													
													} else if ( 'left' == v.wipe[ 'in' ] ) {
													
													}
												}
												if ( v.wipe.out ) {
													if ( 'top' == v.wipe.out ) {
													
													} else if ( 'right' == v.wipe.out ) {
													
													} else if ( 'bottom' == v.wipe.out ) {
													
													} else if ( 'left' == v.wipe.out ) {
													
													}												
												}												
											}
										}
										
									} );
								}
								
							} );
							
							// overlay sprites
							if ( bCurrent ) {						
								var overlaySprites = overlaySpriteHash[ id ];
								if ( overlaySprites ) {
									$.each( overlaySprites, function( k, v ) {
										var overlaySprite = overlayPanel.find( '.' + k );
										if ( overlaySprite.length > 0 ) {
											
											// movement
											if (
												v.start.left || v.start.right || v.start.top || v.start.bottom ||
												v.end.left || v.end.right || v.end.top || v.end.bottom
											) {
											
												var wdt = overlaySprite.width();
												var hgt = overlaySprite.height();
												
												// calculate start and end coordinates
												
												var sl = getLeftPx( v.start.left, v.start.right, wdt, panelWidth );
												var sb = getBottomPx( v.start.bottom, v.start.top, hgt, panelHeight );
												
												var el = getLeftPx( v.end.left, v.end.right, wdt, panelWidth );
												var eb = getBottomPx( v.end.bottom, v.end.top, hgt, panelHeight );
												
												// calculate offset
												var xoff = el - sl;
												var yoff = eb - sb;
												
												// position
												overlaySprite.css( 'left', ( sl + parseInt( xoff * pct ) ) + 'px' );
												overlaySprite.css( 'bottom', ( sb + parseInt( yoff * pct ) ) + 'px' );
												
												/* panel.find( 'span.info' ).html(
													'sl: ' + sl + ', sb: ' + sb + 
													', el: ' + el + ', eb: ' + eb + 
													', xoffpct: ' + parseInt( xoff * pct ) + ', yoffpct: ' + parseInt( yoff * pct )
												); */
											
											}
											
											// rotation
											if ( ( v.start.angle || v.end.angle ) && cssRotationSupported ) {
												var aoff = 0;
												var angle = 0;
												if ( v.start.angle > v.end.angle ) {
													// counter-clockwise
													aoff = v.end.angle - v.start.angle;
													angle = parseInt( v.start.angle - ( aoff * ( pct * -1 ) ) );
												} else {
													// clockwise
													aoff = v.start.angle - v.end.angle;
													angle = parseInt( v.start.angle + ( aoff * ( pct * -1 ) ) );
												}
												// overlaySprite.jqrotate( angle );
												cssSandpaper.setTransform( overlaySprite.get( 0 ), 'rotate(' + angle + 'deg)' );
											}
											
										}
										
									} );
								}
							}
							
						}
						
					} );
					
					elem.data( 'curStep', step );
					
					opts.stepCallback( step );
					
				}
				
							
				// initiate scroll path
				elem.scrollPath( {
					drawPath: opts.drawPath,
					wrapAround: opts.wrapAround,
					scrollBar: opts.scrollBar,
					pathCallback: fPathCallback
				} );
				
				
			} );
			
		}
		
	};
	
} )( jQuery );
/* ==========================================================
 * MobilyMap
 * date: 29.11.2010
 * author: Marcin Dziewulski
 * last update: 20.1.2011
 * web: http://www.mobily.pl or http://playground.mobily.pl
 * email: hello@mobily.pl
 * Free to use under the MIT license.
 * ========================================================== */
( function( $ ) {
	
	$.fn.mobilymap = function( options ) {
		
		var defaults = {
			position: 'center',
			popupClass: 'bubble',
			markerClass: 'point',
			imageAreaSelector: null,
			popup: true,
			cookies: true,
			caption: true,
			setCenter: true,
			navigation: true,
			navSpeed: 1000,
			navBtnClass: 'navBtn',
			outsideButtons: false,
			onMarkerClick: function() { },
			onPopupOpen: function( popup ) { },
			onPopupClose: function() { },
			onMapLoad: function() { }
		};
		
		var sets = $.extend( {}, defaults, options );
		
		return this.each(
			
			function() {
				
				var $this = $( this );
				
				$this.css( {
					position: 'relative',
					overflow: 'hidden',
					cursor: 'move'
				} );
				
				$this.wrapInner( $( '<div \/>' ).addClass( 'imgContent' ).css( { zIndex: '1', position: 'absolute' } ) );
				
				var content = $this.find( '.imgContent' );
				
				var imageArea, title;
				
				if ( sets.imageAreaSelector ) {

					imageArea = content.find( sets.imageAreaSelector );
					title = $this.find( 'img' ).first().attr( 'alt' );
					
				} else {
					
					// default
					imageArea = $this.find( 'img' );
					title = imageArea.attr( 'alt' );
				}
				
				var point = $this.find( '.' + sets.markerClass );
				var mouseDown = false;
				var mx, my, ex, ey;
				var imgw = imageArea.width();
				var imgh = imageArea.height();
				var divw = $this.width();
				var divh = $this.height();
				
				var cookies = {
					create: function( name, value, days ) { 
						if ( days ) {
							var date = new Date();
							date.setTime( date.getTime() + ( days * 24 * 60 * 60 * 1000 ) );
							var expires = '; expires=' + date.toGMTString();
						} else {
							var expires = '';
						}
						
						document.cookie = name + '=' + value + expires + '; path=\/';
					},
					erase: function( name ) {
						cookies.create( name, '', -1 );
					},
					read: function( name ) {
						var nameEQ = name + '=';
						var ca = document.cookie.split( ';' );
						for ( var i = 0; i < ca.length; i++ ) {
							var c = ca[ i ];
							while ( c.charAt( 0 ) == ' ' ) {
								c = c.substring( 1, c.length );
							}
							if ( c.indexOf( nameEQ ) == 0 ) {
								return c.substring( nameEQ.length, c.length );
							}
						}
						return null;
					}
				};
				
				// var yyy = false;
				
				var map = {
					check: function( x, y ) {
						if ( y < ( divh - imgh ) ) {
							y = divh - imgh;
						} else {
							if( y > 0 ) y = 0;
						}
						if ( x < ( divw - imgw ) ) {
							x = divw - imgw;
						} else {
							if ( x > 0 ) x = 0;
						}
						return { x:x, y:y };
					},
					init: function( position ) {
						map.preloader();
						switch( position ) {
							case 'center':
								var x = ( divw - imgw ) / 2;
								var y = ( divh - imgh ) / 2;
								break;
							case 'top left':
								var x = 0;
								var y = 0;
								break;
							case 'top right':
								var x = divw - imgw;
								var y = 0;
								break;
							case 'bottom left':
								var x = 0;
								var y = divh - imgh;
								break;
							case 'bottom right':
								var x = divw - imgw;
								var y = divh - imgh;
								break;
							default:
								var new_position = position.split( ' ' );
								var x = -( new_position[ 0 ] );
								var y = -( new_position[ 1 ] );
								if ( y < ( divh - imgh ) ) {
									y = divh - imgh;
								} else {
									if ( y > 0 ) y = 0;
								}
								if ( x < ( divw - imgw ) ) {
									x = divw - imgw;
								} else {
									if ( x > 0 ) x = 0;
								}
						}
						
						if ( sets.cookies ) {
							if ( cookies.read( 'position') != null ) {
								var pos = cookies.read( 'position').split( ',' );
								var x = pos[ 0 ];
								var y = pos[ 1 ];
							} else {
								var x = ( divw - imgw ) / 2;
								var y = ( divh - imgh ) / 2;
							}
						}
						
						content.css( {
							top: y + 'px',
							left: x + 'px'
						} );
						
					},
					preloader: function() {
						
						var loadimg = new Image();
						
						$this.find( 'img' ).each( function() {
							
							var image = $( this );
							
							var src = image.attr( 'src' );
							image.css( { visibility: 'hidden' } );
							
							$this.append( $( '<div \/>' ).addClass( 'loader' ).css( {
								position: 'absolute',
								zIndex: '10',
								top: '0',
								left: '0',
								width: '100%',
								height: '100%'
							} ) );
							
							$( loadimg ).load( function() {
								image.css( { visibility: 'visible' } );
								$this.find( '.loader' ).fadeOut( 1000, function() {
									$( this ).remove();
									if ( sets.caption ) {
										$this.append( $( '<div \/>' ).addClass( 'imgCaption' ).html( title ).hide() );
										captiond = $this.find( '.imgCaption' );
										captionh = captiond.innerHeight();
										captiond.css( {
											bottom: -captionh + 'px',
											position: 'absolute',
											zIndex: '7'
										} ).show().animate( { bottom: 0 } );
									}
									sets.onMapLoad.call( this );
								} )
							} ).attr( 'src', src );
							
							image.removeAttr( 'alt' );
							
						} );
						
					},
					mouse: function( e ) {
						
						var x, y;
						
						if (
							( ( 'touchmove' === e.type ) || ( 'touchstart' === e.type ) ) && 
							e.originalEvent &&
							e.originalEvent.touches							
						) {
							
							var touch = e.originalEvent.touches[ 0 ];
							x = touch.pageX;
							y = touch.pageY;
														
						} else {
							x = e.pageX;
							y = e.pageY;						
						}
						
						return { x:x, y:y };
					},
					update: function( e ) {
						
						var mouse = map.mouse( e );
						
						var x = mouse.x;
						var y = mouse.y;
						var movex = x - mx;
						var movey = y - my;
						var top = ey + movey;
						var left = ex + movex;
						var check = map.check( left, top );
						
						content.css( {
							top: check.y + 'px',
							left: check.x + 'px'
						} );
						
						if ( sets.cookies ) {
							cookies.create( 'position', check.x + ',' + check.y, 7 );
						}
						
					},
					navigation: {
						buttons: function() {
							$this.prepend( $( '<div \/>' ).addClass( 'mapNav' ).css( {
								position: 'absolute',
								zIndex: '7',
								left: '20px',
								top: '20px'
							} ) );
							nav = $this.find( '.mapNav' );
							for ( i = 0; i < 4; i++ ) {
								nav.append( '<a href="#" class="' + sets.navBtnClass + ' ' + sets.navBtnClass + i + '" rel="' + i + '">btn' + i + '<\/a>' )
							}
							nav.bind( {
								mouseenter: function() {
									if ( sets.caption ) captiond.stop();
								}
							} )
						},
						move: function() {
							$( '.' + sets.navBtnClass ).bind( {
								mousedown: function() {
									var navbtn = $( this ).attr( 'rel' );
									if ( navbtn == 0 ) content.animate( { top: 0 }, sets.navSpeed );
									if ( navbtn == 1 ) content.animate( {left: divw - imgw + 'px' }, sets.navSpeed );
									if ( navbtn == 2 ) content.animate( { top: divh - imgh + 'px' }, sets.navSpeed );
									if ( navbtn == 3 ) content.animate( { left: 0 }, sets.navSpeed );
								},
								mouseup: function() {
									content.stop();
									var pos = content.position();
									var x = pos.left;
									var y = pos.top;
									if ( sets.cookies ) cookies.create( 'position', x + ',' + y, 7 );
								},
								mouseout: function() {
									content.stop();
								},
								click: function() {
									return false;
								}
							} )
						}
					}
				};
				
				if ( sets.navigation ) {
					map.navigation.buttons();
					map.navigation.move();
				}
				
				
				content
					.on( 'mousedown touchstart', function( e ) {
						e.preventDefault();
						mouseDown = true;
						var mouse = map.mouse(e);
						mx = mouse.x;
						my = mouse.y;
						var element = content.position();
						ex = element.left;
						ey = element.top;
						map.update( e );
					} )
					.on( 'mousemove touchmove', function( e ) {
						if ( mouseDown ) map.update( e );
						return false;
					} )
					.on( 'mouseup mouseout touchend', function() {
						if ( mouseDown ) mouseDown = false;
						return false;
					} )
					.on( 'mouseenter', function() {
						if ( sets.caption ) captiond.animate( { bottom: -captionh + 'px' } );
						return false;
					} )
					.on( 'mouseleave', function() {
						if ( sets.caption ) captiond.animate( { bottom: 0 } );
						return false;
					} )
				;
				
				map.init( sets.position );
				point.each( function() {
					var $this = $( this );
					var pos = $this.attr( 'id' ).split( '-' );
					x = pos[ 1 ];
					y = pos[ 2 ];
					$this.css( {
						position: 'absolute',
						zIndex: '2',
						top: y + 'px',
						left: x + 'px'
					} )
				} ).wrapInner( $( '<div \/>' ).addClass( 'markerContent' ).css( { display: 'none' } ) );
				
				point.on( 'click touchstart', function() {
					
					var $this = $( this );
					var pointw = $this.width();
					var pointh = $this.height();
					var pos = $this.position();
					var py = pos.top;
					var px = pos.left;
					var wrap = $this.find( '.markerContent' ).html();
					
					if ( sets.setCenter ) {
						var center_y = -py + divh / 2 - pointh / 2;
						var center_x = -px + divw / 2 - pointw / 2;
						var center = map.check( center_x, center_y );
						content.animate( {
							top: center.y + 'px',
							left: center.x + 'px'
						} );
					}
					
					if ( sets.popup ) {
						
						$( '.' + sets.popupClass ).remove();
						
						$this.after(
							$( '<div \/>' ).addClass( sets.popupClass ).css( {
								position: 'absolute',
								zIndex: '3'
							} ).html( wrap ).append(
								$( '<a \/>' ).addClass( 'close' )
							)
						);
						
						var popup = $this.next( '.' + sets.popupClass );
						var popupw = popup.innerWidth();
						var popuph = popup.innerHeight();
						var y = py;
						var x = px;
						
						sets.onPopupOpen( popup );
						
						popup.css( {
							top: y + pointh + 'px',
							left: x + 'px',
							marginLeft: -( popupw / 2 - pointw / 2 ) + 'px'
						} );
						
					} else {
						sets.onMarkerClick.call( this );
					}
					
					return false;
					
				} );
				
				$this.find( '.close' ).live( 'click', function() {
					
					var $this = $( this );
					
					$this.parents( '.' + sets.popupClass ).remove();
					setTimeout( function() {
						sets.onPopupClose.call( this )
					}, 100 );
					
					return false;
					
				} );
				
				if ( sets.outsideButtons ) {
					$( sets.outsideButtons ).click( function() {
						var $this = $( this );
						var rel = $this.attr( 'rel' );
						div = content.find( '.' + sets.markerClass ).filter( function() {
							return $( this ).attr( 'id') == rel
						} );
						div.click();
						return false;
					} );
				}
			
			}
			
		);
	}
	
}( jQuery ) );
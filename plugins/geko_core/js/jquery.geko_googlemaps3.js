/*
 ### jQuery Google Maps Plugin v1.01 ###
 * Home: http://www.mayzes.org/googlemaps.jquery.html
 * Code: http://www.mayzes.org/js/jquery.googlemaps1.01.js
 * Date: 2010-01-14 (Thursday, 14 Jan 2010)
 * 
 * Dual licensed under the MIT and GPL licenses.
 *   http://www.gnu.org/licenses/gpl.html
 *   http://www.opensource.org/licenses/mit-license.php
 ###
*/
;( function ( $ ) {
	
	//// shorthand references
	
	var GmMap = google.maps.Map;
	var GmLatLng = google.maps.LatLng;
	var GmMarker = google.maps.Marker;
	var GmInfoWin = google.maps.InfoWindow;
	
	var GmSize = google.maps.Size;
	var GmPoint = google.maps.Point;
	
	var GmAnim = google.maps.Animation;
	var GmEvent = google.maps.event;
	
	var GmGeocoder = google.maps.Geocoder;
	var GmGeoStat = google.maps.GeocoderStatus;
	
	var oGeocoder = new GmGeocoder();		// a geocoder instance!!!
	
	
	
	//// helpers
	
	//
	var castToPair = function( value, type ) {
		
		var pair;
		var type;
		
		
		if ( type ) type.toLowerCase();
		
		
		// format value if it is a string
		if ( 'string' === $.type( value ) ) {
			
			pair = value.split( ',' );
			
			pair[ 0 ] = $.trim( pair[ 0 ] );
			pair[ 1 ] = $.trim( pair[ 1 ] );
			
		} else if ( 'array' === $.type( value ) ) {
			
			// already an array
			pair = value;
		}
		
		
		// do stuff if there is a valid pair
		if ( pair ) {
			
			var val1 = pair[ 0 ];
			var val2 = pair[ 1 ];
			
			if ( -1 !== $.inArray( type, [ 'gmsize', 'gsize', 'size' ] ) ) {
				
				value = new GmSize( parseInt( val1 ), parseInt( val2 ) );
				
			} else if ( -1 !== $.inArray( type, [ 'gmlatlng', 'glatlng', 'latlng', 'coords' ] ) ) {
				
				value = new GmLatLng( parseFloat( val1 ), parseFloat( val2 ) );

			} else {
				
				// default GmPoint/GPoint/point
				value = new GmPoint( parseInt( val1 ), parseInt( val2 ) );
			}
			
		}
		
		return value;
	};
	
	
	//
	var formatShape = function( shape ) {
		
		var coords;
		
		if ( 'string' == $.type( shape ) ) {
			
			coords = shape.replace( /;/g, ',' );
			coords = coords.split( ',' );
			
			var i;
			for ( i = 0; i < coords.length; i++ ) {
				coords[ i ] = parseInt( $.trim( coords[ i ] ) );
			}
			
		} else if ( 'array' == $.type( shape ) ) {
			
			// already an array
			coords = shape;
		}
		
		
		//
		if ( coords ) {
			
			var shapeType = 'poly';
			
			if ( 3 === coords.length ) {
				shapeType = 'circle';
			} else if ( 4 === coords.length ) {
				shapeType = 'rect';
			}
			
			// formatted shape
			shape = {
				coords: coords,
				type: shapeType
			};
		}
		
		return shape;
	};
	
	
	
	// google map wrapper class
	GekoGMap3 = function( elem, opts ) {
		
		//// set-up options
		
		if ( opts.latitude && opts.longitude ) {
			
			opts.center = new GmLatLng(
				parseFloat( opts.latitude ),
				parseFloat( opts.longitude )
			);
			
			delete opts.latitude;
			delete opts.longitude;
		}
		
		if ( opts.depth ) {
			
			opts.zoom = opts.depth;
			delete opts.depth;
		}
		
		if ( opts.controls.hide ) {
			
			opts.disableDefaultUI = true;
			delete opts.controls.hide;
		}
		
		
		
		//// initialize google map
		
		var gMap = new GmMap( elem, opts );
		this.gMap = gMap;
		
		// re-declare for use by methods
		var _this = this;
		
		this.markers = {};
		this.markerHash = {};

		
		
		
		//// methods
		
		var iMarkerIdx = 0;
		
		// format marker params
		this.mapMarkersOptions = function( params ) {

			var markerParams = { map: gMap };
			
			
			// title
			if ( params.title ) {
				markerParams.title = params.title;
			}
			
			//// icon
			
			if ( params.icon ) {
				
				var icon = params.icon;
				
				var iconParams = {};
				
				if ( icon.image ) {
					iconParams.url = icon.image;
				}
				
				if ( icon.iconSize ) {
					iconParams.size = castToPair( icon.iconSize, 'size' );
				}
				
				if ( icon.iconOrigin ) {
					iconParams.origin = castToPair( icon.iconOrigin );					
				}

				if ( icon.iconAnchor ) {
					iconParams.anchor = castToPair( icon.iconAnchor );					
				}
									
				markerParams.icon = iconParams;
			}
			
			
			//// shape
			
			if ( params.shape ) {
				markerParams.shape = formatShape( params.shape );
			}
			
			
			// order/zIndex
			
			if ( params.order ) {
				markerParams.zIndex = parseInt( params.order );
			}
			
			
			//// animation
			
			if ( params.animation ) {
				
				var anim = params.animation.toLowerCase();
				var animType;
				
				if ( 'drop' === anim ) {
					animType = GmAnim.DROP;
				} else if ( 'bounce' === anim ) {
					animType = GmAnim.BOUNCE;					
				}
				
				if ( animType ) {
					markerParams.animation = animType;
				}
			}
			
			return markerParams;
		};

		
		// add a marker
		
		var iMarkerIdx = 0;
		
		this.addMapMarkerInfo = function ( marker, params ) {
			
			_this.markers[ iMarkerIdx ] = marker;
			
			
			//// info box
			
			if ( params.info && ( params.info.layer || params.info.content ) ) {
				
				var sContent;
				
				if ( params.info.layer ) {

					var infoLayerId = params.info.layer;
	
					// Hide Div Layer With Info Window HTML
					var eInfoDiv = $( infoLayerId );
					
					sContent = eInfoDiv.html();
					
				} else if ( params.info.content ) {
					
					sContent = params.info.content;
				}
				
				
				// set up info window if there is content
				if ( sContent ) {
					
					var infoWin = new GmInfoWin( {
						content: sContent
					} );
					
					// open info box on load
					if ( params.info.popup ) {
						infoWin.open( gMap, marker );
					}
					
					GmEvent.addListener( marker, 'click', function() {
						infoWin.open( gMap, marker );
					} );
					
				}
				
			}
			
			
			//// create hash for use with getMarker()
			
			if ( params.icon && params.icon.id ) {
				_this.markerHash[ params.icon.id ] = iMarkerIdx;
			}
			
			if ( params.id ) {
				_this.markerHash[ params.id ] = iMarkerIdx;
			}
			
			iMarkerIdx++;
		};
		
		
		// map a bunch of markers
		this.mapMarkers = function( markers ) {
			
			if ( typeof( markers.length ) == 'undefined' ) {
				// One marker only. Parse it into an array for consistency.
				markers = [ markers ];
			}
			
			var i, j;
			
			for ( i = 0; i < markers.length; i++ ) {
				
				var params = markers[ i ];
				
				var markerParams = _this.mapMarkersOptions( params );
				
				
				//// position
				
				//
				if ( params.latitude && params.longitude ) {
					
					// just one
					markerParams.position = new GmLatLng(
						parseFloat( params.latitude ),
						parseFloat( params.longitude )
					);
					
					var marker = new GmMarker( markerParams );
					_this.addMapMarkerInfo( marker, params );
					
					
				} else if ( params.geocode ) {
					
					// possibly more than one, or none
					oGeocoder.geocode(
						
						{ address: params.geocode },
						
						function( results, status ) {
							
							if ( status === GmGeoStat.OK ) {
								
								for ( j = 0; j < results.length; j++ ) {
									
									var res = results[ j ];
									
									markerParams.position = res.geometry.location;
									
									if ( !params.title ) {
										markerParams.title = res.formatted_address;
									}
									
									
									var marker = new GmMarker( markerParams );
									_this.addMapMarkerInfo( marker, params );
									
								}
								
							} else {
							
								alert( 'Geocode was not successful for the following reason: %s', status );
							}
							
						}
						
					);
					
					
				}
				
								
			}
			
		};
		
		
		// resposition the map
		this.geoCode = function( options ) {
			
			// possibly more than one, or none
			oGeocoder.geocode(
				
				{ address: options.address },
				
				function( results, status ) {
					
					if ( results && ( results.length > 0 ) ) {
						
						var oRes = results[ 0 ];
						
						var oGeo = oRes.geometry;
						
						var oLoc = oGeo.location;
						var oViewport = oGeo.viewport;
						var oBounds = oGeo.bounds;
						
						gMap.setCenter( new GmLatLng( oLoc.lat(), oLoc.lng() ) );
						
						gMap.fitBounds( oBounds );
						
						if ( opts.geoCodeZoomAdjustment ) {
							gMap.setZoom( gMap.getZoom() + opts.geoCodeZoomAdjustment );
						}
						
						// console.log( oGeo );
						// console.log( [ oLoc.lat(), oLoc.lng() ] );
						// console.log( status );
					}
					
				}
			
			);
			
		};
		
		
		//// do stuff
		
		if ( opts.markers ) {
			this.mapMarkers( opts.markers );
		}
		
		
		
	}
	
	
	//// class properties
	
	GekoGMap3.defaults = {
		// Default Map Options
		latitude: 37.4419,
		longitude: -122.1419,
		depth: 13,
		controls: {
			hide: false
		}
	}
	
	
	
	
	//// jquery plugin function
	
	$.fn.gekoGooglemaps3 = function( options ) {
		
		if ( 'string' === $.type( options ) ) {
			
			// return reference to GekoGMap3 instance
			if ( 'gmap' === options ) {
				return $( this ).data( 'geko-jquery-gmap3' );
			}
			
		} else {
		
			// Fill default values where not set by instantiation code
			var opts = $.extend( {}, GekoGMap3.defaults, options );
			
			// $.fn.googleMaps.includeGoogle( opts.key, opts.sensor );
			return this.each( function() {
				$( this ).data( 'geko-jquery-gmap3', new GekoGMap3( this, opts ) );
			} );
		
		}
		
	};

	
	
} )( jQuery );

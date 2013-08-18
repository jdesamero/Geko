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

	$.fn.googleMaps = function( options ) {
	
		if ( !window.GBrowserIsCompatible || !GBrowserIsCompatible() )  {
		   return this;
		}
	
		// Fill default values where not set by instantiation code
		var opts = $.extend( {}, jQueryGMap.defaults, options );
		
		//$.fn.googleMaps.includeGoogle( opts.key, opts.sensor );
		return this.each( function() {
			$( this ).data( 'jquery-gmap', new jQueryGMap( this, opts ) );
		} );
		
	};
	
	jQueryGMap = function( elem, opts ) {
		
		//// properties
		
		this.gMap = new GMap2( elem, opts );
			
		this.directions = {};
		
		this.latitude = '';
		this.longitude = '';
		this.latlong = {};
		this.maps = {};
		
		this.marker = {};
		this.markerHash = {};
		
		
		
		// re-declare for use by methods
		var jMap = this;
		var gMap = this.gMap;
		
		
		
		
		
		//// methods
		
		this.mapsConfiguration = function( opts ) {
			
			// Set min/max zoom
			var mapTypes = gMap.getMapTypes();
			$.each( mapTypes, function() {
				
				if ( opts.minZoom ) {
					this.getMinimumResolution = function() {
						return opts.minZoom;
					}
				}
			
				if ( opts.maxZoom ) {
					this.getMaximumResolution = function() {
						return opts.maxZoom;
					}	
				}
				
			} );
			
			
			// GEOCODE
			if ( opts.geocode ) {
				geocoder = new GClientGeocoder();
				geocoder.getLatLng( opts.geocode, function( center ) {
					if ( !center ) {
						alert( address + ' not found' );
					} else {
						gMap.setCenter( center, opts.depth );
						jMap.latitude = center.x;
						jMap.longitude = center.y;
					}
				} );
			} else {
				// Latitude & Longitude Center Point
				var center = new GLatLng( opts.latitude, opts.longitude );
				// Set the center of the Map with the new Center Point and Depth
				gMap.setCenter( center, opts.depth );
			}
			
			// POLYLINE
			if ( opts.polyline ) {
				// Draw a PolyLine on the Map
				gMap.addOverlay( jMap.mapPolyLine( opts.polyline ) );
			}
			
			// GEODESIC 
			if ( opts.geodesic ) {
				jMap.mapGeoDesic( opts.geodesic );
			}
			
			// PAN
			if ( opts.pan ) {
				// Set Default Options
				opts.pan = jMap.mapPanOptions( opts.pan );
				// Pan the Map
				window.setTimeout( function() {
					gMap.panTo( new GLatLng( opts.pan.panLatitude, opts.pan.panLongitude ) );
				}, opts.pan.timeout );
			}
			
			// LAYER
			if ( opts.layer ) {
				// Set the Custom Layer
				gMap.addOverlay( new GLayer( opts.layer ) );
			}
			
			// MARKERS
			if ( opts.markers ) {
				jMap.mapMarkers( opts.markers );
			}
			
			// CONTROLS
			if ( opts.controls.type || opts.controls.zoom ||  opts.controls.mapType ) {
				jMap.mapControls(opts.controls);
			} else {
				if ( !opts.controls.hide ) {
					gMap.setUIToDefault();
				}
			}
			
			// SCROLL
			if ( opts.scroll ) {
				gMap.enableScrollWheelZoom();
			} else if ( !opts.scroll ) {
				gMap.disableScrollWheelZoom();
			}
			
			// LOCAL SEARCH
			if ( opts.controls.localSearch ) {
				gMap.enableGoogleBar();
			} else {
				gMap.disableGoogleBar();
			}
			
			// FEED (RSS/KML)
			if ( opts.feed ) {
				gMap.addOverlay( new GGeoXml( opts.feed ) );
			}
			
			// TRAFFIC INFO
			if ( opts.trafficInfo ) {
				var trafficOptions = { incidents:true };
				trafficInfo = new GTrafficOverlay( trafficOptions );
				gMap.addOverlay( trafficInfo );
			}
			
			// DIRECTIONS
			if ( opts.directions ) {
				jMap.directions = new GDirections( gMap, opts.directions.panel );
				jMap.directions.load( opts.directions.route );
			}
			
			if ( opts.streetViewOverlay ) {
				svOverlay = new GStreetviewOverlay();
				gMap.addOverlay( svOverlay );	
			}
		}
		
		this.mapGeoDesic = function( options ) {
			
			// Default GeoDesic Options
			geoDesicDefaults = {
				startLatitude: 	37.4419,
				startLongitude: -122.1419,
				endLatitude:	37.4519,
				endLongitude:	-122.1519,
				color: 			'#ff0000',
				pixels: 		2,
				opacity: 		10
			}
			
			// Merge the User & Default Options
			options = $.extend( {}, geoDesicDefaults, options );
			var polyOptions = { geodesic: true };
			var polyline = new GPolyline(
				[ 
					new GLatLng( options.startLatitude, options.startLongitude ),
					new GLatLng( options.endLatitude, options.endLongitude )
				],
				options.color, options.pixels, options.opacity, polyOptions
			);
			
			gMap.addOverlay( polyline );
		}
		
		this.localSearchControl = function( options ) {
			var controlLocation = jMap.mapControlsLocation( options.location );
			gMap.addControl( new gMap.LocalSearch(), new GControlPosition( controlLocation, new GSize( options.x,options.y ) ) );
		}
		
		this.getLatitude = function() {
			return jMap.latitude;
		}
		
		this.getLongitude = function() {
			return jMap.longitude;
		}
			
		this.mapPolyLine = function( options ) {
			
			// Default PolyLine Options
			polylineDefaults = {
				startLatitude: 37.4419,
				startLongitude: -122.1419,
				endLatitude: 37.4519,
				endLongitude: -122.1519,
				color: '#ff0000',
				pixels: 2
			}
			
			// Merge the User & Default Options
			options = $.extend( {}, polylineDefaults, options );
			
			//Return the New Polyline
			return new GPolyline(
				[
					new GLatLng( options.startLatitude, options.startLongitude ),
					new GLatLng( options.endLatitude, options.endLongitude )
				], 
				options.color, 
				options.pixels
			);
		}
		
		this.mapPanOptions = function( options ) {
			
			// Returns Panning Options
			var panDefaults = {
				panLatitude:	37.4569, 	
				panLongitude:	-122.1569,
				timeout: 		0
			}
			
			return options = $.extend( {}, panDefaults, options );
		}
		
		this.mapMarkersOptions = function( icon ) {
			
			//Define an icon
			var gIcon = new GIcon( G_DEFAULT_ICON );	
			
			if ( icon.image ) {
				// Define Icons Image
				gIcon.image = icon.image;
			}
			
			if ( icon.shadow ) {
				// Define Icons Shadow
				gIcon.shadow = icon.shadow;
			}
			
			if ( icon.iconSize ) {
				// Define Icons Size
				icon.iconSize = jQueryGMap.castToPair( icon.iconSize, 'GSize' );
				gIcon.iconSize = new GSize( icon.iconSize.width, icon.iconSize.height );
			}
			
			if ( icon.shadowSize ) {
				// Define Icons Shadow Size
				icon.shadowSize = jQueryGMap.castToPair( icon.shadowSize, 'GSize' );
				gIcon.shadowSize = new GSize( icon.shadowSize.width, icon.shadowSize.height );
			}
			
			if ( icon.iconAnchor ) {			
				// Define Icons Anchor
				icon.iconAnchor = jQueryGMap.castToPair( icon.iconAnchor );
				gIcon.iconAnchor = new GPoint( icon.iconAnchor.x, icon.iconAnchor.y );
			}
			
			if ( icon.infoWindowAnchor ) {
				// Define Icons Info Window Anchor
				icon.infoWindowAnchor = jQueryGMap.castToPair( icon.infoWindowAnchor );
				gIcon.infoWindowAnchor = new GPoint( icon.infoWindowAnchor.x, icon.infoWindowAnchor.y );
			}
			
			if ( icon.dragCrossImage ) {
				// Define Drag Cross Icon Image
				gIcon.dragCrossImage = icon.dragCrossImage;
			}
			
			if ( icon.dragCrossSize ) {
				// Define Drag Cross Icon Size
				icon.dragCrossSize = jQueryGMap.castToPair( icon.dragCrossSize, 'GSize' );
				gIcon.dragCrossSize = new GSize( icon.dragCrossSize.width, icon.dragCrossSize.height );
			}
			
			if ( icon.dragCrossAnchor ) {
				// Define Drag Cross Icon Anchor
				icon.dragCrossAnchor = jQueryGMap.castToPair( icon.dragCrossAnchor );
				gIcon.dragCrossAnchor = new GPoint( icon.dragCrossAnchor.x, icon.dragCrossAnchor.y );
			}
			
			if ( icon.maxHeight ) {
				// Define Icons Max Height
				gIcon.maxHeight = icon.maxHeight;
			}
			
			if ( icon.PrintImage ) {
				// Define Print Image
				gIcon.PrintImage = icon.PrintImage;
			}
			
			if ( icon.mozPrintImage ) {
				// Define Moz Print Image
				gIcon.mozPrintImage = icon.mozPrintImage;
			}
			
			if ( icon.PrintShadow ) {
				// Define Print Shadow
				gIcon.PrintShadow = icon.PrintShadow;
			}
			
			if ( icon.transparent ) {
				// Define Transparent
				gIcon.transparent = icon.transparent;
			}
			
			if ( icon.imageMap ) {
				// Define Image Map
				gIcon.imageMap = icon.imageMap;
			}
			
			return gIcon;
		}
		
		this.mapMarkers = function( markers ) {
			
			if ( typeof( markers.length ) == 'undefined' ) {
				// One marker only. Parse it into an array for consistency.
				markers = [ markers ];
			}
			
			for ( var i = 0; i < markers.length; i++ ) {
				
				var params = markers[ i ];
				
				if ( params.icon ) {
					
					params.idx = i;
					var gIcon = jMap.mapMarkersOptions( params.icon );
					
					if ( params.geocode ) {
						
						this.addGeocodeMarker( gIcon, params );
						
					} else if ( params.latitude && params.longitude ) {
						// Latitude & Longitude Center Point
						var center = new GLatLng( params.latitude, params.longitude );
						var gMarker = new GMarker( center, { draggable: params.draggable, icon: gIcon } );
						this.addMapMarkerOverlay( gMarker, params );
					}
					
				}
								
			}
			
		}
		
		this.addGeocodeMarker = function( gIcon, params ) {
			var geocoder = new GClientGeocoder();
			geocoder.getLatLng( params.geocode, function( center ) {
				if ( !center ) {
					alert( address + ' not found' );
				} else {
					var gMarker = new GMarker( center, { draggable: params.draggable, icon: gIcon } );
					jMap.addMapMarkerOverlay( gMarker, params );
				}
			} );			
		}
		
		this.addMapMarkerOverlay = function ( gMarker, params ) {

			gMap.addOverlay( gMarker );
			
			if ( params.info ) {
				
				if ( params.info.layer ) {
					
					var infoLayerId = params.info.layer;
					
					// Hide Div Layer With Info Window HTML
					$( infoLayerId ).hide();
					
					// Marker Div Layer Exists
					if ( params.info.customStyle ) {
						
						var customStyle = params.info.customStyle;
						var showEvent = null;
						var hideEvent = null;
						
						gMarker.infoLayerId = infoLayerId;
						
						if ( 'hover' == params.info.event ) {
							showEvent = 'mouseover';
							hideEvent = 'mouseout';
						} else {
							showEvent = 'click';
						}
						
						// show custom info window
						if ( showEvent ) {
							GEvent.addListener( gMarker, showEvent, function() {
								if ( gMap.customInfo ) gMap.removeOverlay( gMap.customInfo );
								gMap.customInfo = new customStyle( this.getLatLng(), $( this.infoLayerId ).html() );
								gMap.addOverlay( gMap.customInfo );
							} );
						}
						
						// hide custom info window
						if ( hideEvent ) {
							GEvent.addListener( gMarker, hideEvent, function() {
								if ( gMap.customInfo ) gMap.removeOverlay( gMap.customInfo );
							} );
						}
						
						//
						if ( params.info.popup ) GEvent.trigger( gMarker, showEvent );
						
					} else {
						
						if ( params.info.popup ) {
							// Map Marker Shows an Info Box on Load
							gMarker.openInfoWindowHtml( $( infoLayerId ).html() );
						} else {
							gMarker.bindInfoWindowHtml( $( infoLayerId ).html() );
						}
						
					}
					
				}
				
			}
			
			jMap.marker[ params.idx ] = gMarker;
			
			// create hash for use with getMarker()
			if ( params.icon.id ) {
				jMap.markerHash[ params.icon.id ] = params.idx;
			}
			
		}
		
		this.mapControlsLocation = function ( location ) {
			switch (location) {
				case 'G_ANCHOR_TOP_RIGHT' :
					return G_ANCHOR_TOP_RIGHT;
				break;
				case 'G_ANCHOR_BOTTOM_RIGHT' :
					return G_ANCHOR_BOTTOM_RIGHT;
				break;
				case 'G_ANCHOR_TOP_LEFT' :
					return G_ANCHOR_TOP_LEFT;
				break;
				case 'G_ANCHOR_BOTTOM_LEFT' :
					return G_ANCHOR_BOTTOM_LEFT;
				break;
			}
			return;
		}
		
		this.mapControl = function( control ) {
			switch ( control ) {
				case 'GLargeMapControl3D' :
					return new GLargeMapControl3D();
				break;
				case 'GLargeMapControl' :
					return new GLargeMapControl();
				break;
				case 'GSmallMapControl' :
					return new GSmallMapControl();
				break;
				case 'GSmallZoomControl3D' :
					return new GSmallZoomControl3D();
				break;
				case 'GSmallZoomControl' :
					return new GSmallZoomControl();
				break;
				case 'GScaleControl' :
					return new GScaleControl();
				break;
				case 'GMapTypeControl' :
					return new GMapTypeControl();
				break;
				case 'GHierarchicalMapTypeControl' :
					return new GHierarchicalMapTypeControl();
				break;
				case 'GOverviewMapControl' :
					return new GOverviewMapControl();
				break;
				case 'GNavLabelControl' :
					return new GNavLabelControl();
				break;
			}
			return;
		}
			
		this.mapControls = function( options ) {
			
			// Default Controls Options
			controlsDefaults = {
				type: {
					location: 'G_ANCHOR_TOP_RIGHT',
					x: 10,
					y: 10,
					control: 'GMapTypeControl'
				},
				zoom: {
					location: 'G_ANCHOR_TOP_LEFT',
					x: 10,
					y: 10,
					control: 'GLargeMapControl3D'
				}
			};
			
			// Merge the User & Default Options
			options = $.extend( {}, controlsDefaults, options );
			options.type = $.extend( {}, controlsDefaults.type, options.type );
			options.zoom = $.extend( {}, controlsDefaults.zoom, options.zoom );
			
			if ( options.type ) {
				var controlLocation = jMap.mapControlsLocation( options.type.location );
				var controlPosition = new GControlPosition( controlLocation, new GSize( options.type.x, options.type.y ) );
				gMap.addControl( jMap.mapControl( options.type.control ), controlPosition );
			}
			
			if ( options.zoom ) {
				var controlLocation = jMap.mapControlsLocation( options.zoom.location );
				var controlPosition = new GControlPosition( controlLocation, new GSize( options.zoom.x, options.zoom.y ) )
				gMap.addControl( jMap.mapControl( options.zoom.control ), controlPosition );
			}
			
			if ( options.mapType ) {
				if ( options.mapType.length >= 1 ) {
					for ( var i = 0; i < options.mapType.length; i++ ) {
						if ( options.mapType[ i ].remove )
							gMap.removeMapType( jQueryGMap.mapTypeControl( options.mapType[ i ].remove ) );
						if ( options.mapType[ i ].add )
							gMap.addMapType( jQueryGMap.mapTypeControl( options.mapType[ i ].add ) );
					}
				} 
				else {
					if ( options.mapType.add )
						gMap.addMapType( jQueryGMap.mapTypeControl( options.mapType.add ) );
					if ( options.mapType.remove )
						gMap.removeMapType( jQueryGMap.mapTypeControl( options.mapType.remove ) );
				}
			}
		}
		
		this.geoCode = function( options ) {
			geocoder = new GClientGeocoder();
			
			geocoder.getLatLng( options.address, function( point ) {
				if ( !point ) {
					alert( address + ' not found' );
				} else {
					gMap.setCenter( point, options.depth );
				}
			} );
		}
		
		this.getMarker = function( id ) {
			return this.marker[ this.markerHash[ id ] ];
		}
		
		
		//// initialize
		this.mapsConfiguration( opts );
	
		GEvent.addListener( this.gMap, 'click', function( overlay ) {
			
			if ( overlay ) return;
			if ( gMap.customInfo )  gMap.removeOverlay( gMap.customInfo );
			
			gMap.customInfo = null;
			
		} );
		
		
	}
	
	
	//// class properties
	
	jQueryGMap.defaults = {
		// Default Map Options
		latitude: 37.4419,
		longitude: -122.1419,
		depth: 13,
		scroll: true,
		trafficInfo: false,
		streetViewOverlay: false,
		controls: {
			hide: false,
			localSearch: false
		},
		layer: null,
		minZoom: null,
		maxZoom: null
	}
	
	jQueryGMap.mapTypes = {
		G_NORMAL_MAP: G_NORMAL_MAP,
		G_SATELLITE_MAP: G_SATELLITE_MAP,
		G_HYBRID_MAP: G_HYBRID_MAP
	}
	
	
	//// class methods
	
	jQueryGMap.registerMapType = function ( sKey, oMapType ) {
		jQueryGMap.mapTypes[ sKey ] = oMapType;
	}
	
	jQueryGMap.mapTypeControl = function( type ) {
		if ( !jQueryGMap.mapTypes[ type ] ) return;
		return jQueryGMap.mapTypes[ type ];
	}
	
	//
	jQueryGMap.castToPair = function( value, type ) {
		
		// format value if it is a string
		if ( value.constructor.toString().match( /string/i ) ) {
			
			var pair = value.split(',');
			var val1 = parseInt( $.trim( pair[ 0 ] ) );
			var val2 = parseInt( $.trim( pair[ 1 ] ) );
			
			if ( 'GSize' == type || 'size' == type ) {
				value = { width: val1, height: val2 }		
			} else {
				// default GPoint/point
				value = { x: val1, y: val2 }
			}
			
		}
		
		return value;
	}
	
} )( jQuery );

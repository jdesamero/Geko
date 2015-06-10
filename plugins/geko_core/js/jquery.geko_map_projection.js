( function( $ ) {

	var oProjections = {
		'aitoff': { label: 'Aitoff', func: 'aitoff' },
		'albers': { label: 'Albers', func: 'albers' },
		'august': { label: 'August', func: 'august' },
		'baker': { label: 'Baker', func: 'baker' },
		'berghaus': { label: 'Berghaus', func: 'berghaus' },
		'boggs': { label: 'Boggs', func: 'boggs' },
		'bonne': { label: 'Bonne', func: 'bonne' },
		'bromley': { label: 'Bromley', func: 'bromley' },
		'collignon': { label: 'Collignon', func: 'collignon' },
		'craster': { label: 'Craster Parabolic', func: 'craster' },
		'eckert_1': { label: 'Eckert I', func: 'eckert1' },
		'eckert_2': { label: 'Eckert II', func: 'eckert2' },
		'eckert_3': { label: 'Eckert III', func: 'eckert3' },
		'eckert_4': { label: 'Eckert IV', func: 'eckert4' },
		'eckert_5': { label: 'Eckert V', func: 'eckert5' },
		'eckert_6': { label: 'Eckert VI', func: 'eckert6' },
		'eisenlohr': { label: 'Eisenlohr', func: 'eisenlohr' },
		'equirectangular': { label: 'Equirectangular (Plate Carr&eacute;e)', func: 'equirectangular' },
		'fahey': { label: 'Fahey', func: 'fahey' },
		'cylindrical_stereographic': { label: 'Gall Stereographic', func: 'cylindricalStereographic' },
		'homolosine': { label: 'Goode Homolosine', func: 'homolosine' },
		'ginzburg_4': { label: 'Ginzburg IV', func: 'ginzburg4' },
		'ginzburg_5': { label: 'Ginzburg V', func: 'ginzburg5' },
		'ginzburg_6': { label: 'Ginzburg VI', func: 'ginzburg6' },
		'ginzburg_8': { label: 'Ginzburg VIII', func: 'ginzburg8' },
		'ginzburg_9': { label: 'Ginzburg IX', func: 'ginzburg9' },
		'gringorten': { label: 'Gringorten', func: 'gringorten' },
		'guyou': { label: 'Guyou', func: 'guyou' },
		'hammer': { label: 'Hammer', func: 'hammer' },
		'hammer_retroazimuthal': { label: 'Hammer Retroazimuthal', func: 'hammerRetroazimuthal' },
		'healpix': { label: 'HEALPix', func: 'healpix' },
		'hill': { label: 'Hill', func: 'hill' },
		'kavrayskiy_7': { label: 'Kavrayskiy VII', func: 'kavrayskiy7' },
		'lagrange': { label: 'Lagrange', func: 'lagrange' },
		'cylindricalEqualArea': { label: 'Lambert Cylindrical Equal-area', func: 'cylindricalEqualArea' },
		'larrivee': { label: 'Larriv&eacute;e', func: 'larrivee' },
		'laskowski': { label: 'Laskowski', func: 'laskowski' },
		'loximuthal': { label: 'Loximuthal', func: 'loximuthal' },
		'mercator': { label: 'Mercator', func: 'mercator' },
		'miller': { label: 'Miller', func: 'miller' },
		'mt_flat_polar_parabolic': { label: 'McBryde-Thomas Flat-Polar Parabolic', func: 'mtFlatPolarParabolic' },
		'mt_flat_polar_quartic': { label: 'McBryde-Thomas Flat-Polar Quartic', func: 'mtFlatPolarQuartic' },
		'mt_flat_polar_sinusoidal': { label: 'McBryde-Thomas Flat-Polar Sinusoidal', func: 'mtFlatPolarSinusoidal' },
		'mollweide': { label: 'Mollweide', func: 'mollweide' },
		'natural_earth': { label: 'naturalEarth', func: 'naturalEarth' },
		'nell_hammer': { label: 'Nell-Hammer', func: 'nellHammer' },
		'orthographic': { label: 'Orthographic', func: 'orthographic' },
		'polyconic': { label: 'Polyconic', func: 'polyconic' },
		'rectangular_polyconic': { label: 'Rectangular Polyconic', func: 'rectangularPolyconic' },
		'robinson': { label: 'Robinson', func: 'robinson' },
		'sinusoidal': { label: 'Sinusoidal', func: 'sinusoidal' },
		'sinu_mollweide': { label: 'Sinu-Mollweide', func: 'sinuMollweide' },
		'stereographic': { label: 'Stereographic', func: 'stereographic' },
		'times': { label: 'Times', func: 'times' },
		'van_der_grinten': { label: 'Van der Grinten', func: 'vanDerGrinten' },
		'van_der_grinten_2': { label: 'Van der Grinten II', func: 'vanDerGrinten2' },
		'van_der_grinten_3': { label: 'Van der Grinten III', func: 'vanDerGrinten3' },
		'van_der_grinten_4': { label: 'Van der Grinten IV', func: 'vanDerGrinten4' },
		'wagner_4': { label: 'Wagner 4', func: 'wagner4' },
		'wagner_6': { label: 'Wagner 6', func: 'wagner6' },
		'wagner_7': { label: 'Wagner 7', func: 'wagner7' },
		'waterman': { label: 'Waterman', func: 'waterman' },
		'winkel_tripel': { label: 'Winkel Tripel', func: 'winkel3' }
	};
	
	//
	$.gekoMapProjection = function( options ) {
		
		var _this = this;
		
		var iD3DefWdt = 960;
		var iD3DefHgt = 500;
		
		var opts = $.extend( {
			
			width: iD3DefWdt,
			height: iD3DefHgt,
			
			type: 'mercator',
			
			xoffset: 0,
			yoffset: 0,
			
			marker_wdt: null,
			marker_hgt: null,
			
			fit: 'crop'			// or zoom
			
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


			// d3_geo_projection stuff:
			//    default map width/height: 960 x 500
			//    center: 480 x 250
			//    scale: 150
			//    methods: translate(), scale()
			
			
			
			var aRes = null;
						
			var fnProj = oProjections[ sType ];
			
			if ( !fnProj ) {
				fnProj = oProjections[ 'mercator' ];
			}
			
			fnProj = d3.geo[ fnProj.func ]();
			
			var iCmpX = iWdt / iD3DefWdt;
			var iCmpY = iHgt / iD3DefHgt;
			
			
			if ( iCmpX > iCmpY ) {
				
				// left/right of map will be cropped
				iScale = iCmpY * 150;
				
			} else {
				
				// top/bottom of map will be cropped
				iScale = iCmpX * 150;
				
			}
			
			
			// calculate given map projection
			
			if ( 150 != iScale ) {
				fnProj.scale( iScale );
				fnProj.translate( [ Math.round( iWdt / 2 ), Math.round( iHgt / 2 ) ] );
			}
			
			aRes = fnProj( [ fLon, fLat ] );
			
			iXpos = aRes[ 0 ];
			iYpos = aRes[ 1 ];
			
			
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
	
	
	$.gekoMapProjection.projections = oProjections;
	
	
} )( jQuery );
;( function ( $ ) {
	
	$.gekoWpLocation = function( options ) {
		
		var opts = $.extend( {
			prefix: '',
			continent_id: 'continent_id',
			country_id: 'country_id',
			province_id: 'province_id',
			city_list_id: 'city_list',
			closest: 'tr'					// closest container to hide (which encompases the label as well)
		}, options );
		
		
		var updateLocationControls = function( sPrefix ) {
			
			var sCurrSet, sCurrSel;
			var sContId = '#' + sPrefix + opts.continent_id;
			var sCounId = '#' + sPrefix + opts.country_id;
			var sProvId = '#' + sPrefix + opts.province_id;
			var sCityId = '#' + sPrefix + opts.city_list_id;
			
			if ( $( sContId ).length ) {
				if ( $( sContId ).val() ) {
					sCurrSet = 'continent-' + $( sContId ).val();
					sCurrSel = '.default, .' + sCurrSet;
					$( sCounId ).closest( opts.closest ).show();
					$( sCounId ).showSelOpts( sCurrSel );
				} else {
					$( sCounId ).val( '' );
					$( sCounId ).closest( opts.closest ).hide();
				}
			}
			
			if ( $( sCounId ).length ) {
				if ( $( sCounId ).val() ) {
					sCurrSet = 'country-' + $( sCounId ).val();
					sCurrSel = '.default, .' + sCurrSet;
					$( sProvId ).closest( opts.closest ).show();
					$( sProvId ).showSelOpts( sCurrSel );
				} else {
					$( sProvId ).val( '' );
					$( sProvId ).closest( opts.closest ).hide();
				}
			}

			if ( $( sProvId ).length && ( sCityId ).length ) {
				if ( $( sProvId ).val() ) {
					sCurrSet = 'province-' + $( sProvId ).val();
					sCurrSel = '.default, .' + sCurrSet;
					$( sCityId ).closest( opts.closest ).show();
					$( sCityId ).showSelOpts( sCurrSel );
				} else {
					$( sCityId ).val( '' );
					$( sCityId ).closest( opts.closest ).hide();
				}
			}
			
		};
		
		// add functionality
		
		var aOpts;
		var sContId = '#' + opts.prefix + opts.continent_id;
		var sCounId = '#' + opts.prefix + opts.country_id;
		var sProvId = '#' + opts.prefix + opts.province_id;
		var sCityId = '#' + opts.prefix + opts.city_list_id;
		
		if ( $( sContId ).length ) {
			
			$( sContId ).change( function () {
				$( sCounId ).val( '' );
				updateLocationControls( opts.prefix );
			} );
			
		}
		
		if ( $( sCounId ).length ) {				
			$( sCounId ).change( function () {
				$( sProvId ).val( '' );
				updateLocationControls( opts.prefix );
			} );
		}

		if ( $( sProvId ).length ) {
			$( sProvId ).change( function () {
				if ( $( sCityId ).length ) $( sCityId ).val( '' );
				updateLocationControls( opts.prefix );
			} );
		
		}

		if ( $( sCityId ).length ) {
			$( sCityId ).change( function () {
				updateLocationControls( opts.prefix );
			} );
		}
		
		// initialize
		
		updateLocationControls( opts.prefix );
		
		return this;
	};
	
} )( jQuery );
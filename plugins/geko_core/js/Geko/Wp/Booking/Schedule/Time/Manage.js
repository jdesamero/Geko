;( function ( $ ) {
	
	$.gekoWpBookingScheduleTimeManage = function( options ) {
		
		var opts = $.extend( {
			// ...
		}, options );
		
		$( opts.form_sel ).submit( function() {
			var form = $( this );
			var formParent = form.parent();
			var bstErrorDiv = formParent.find( '#bst_error' );
			if ( bstErrorDiv.length > 0 ) {
				bstErrorDiv.fadeOut( 300 ).remove();
			}
			
			var errors = '';
			form.find( 'input, textarea, select' ).removeClass( 'error_field' );
			
			var dailyGroups = {};
			
			// go through each row
			$( '.multi_row table tr' ).each( function() {

				var row = $( this );
				
				if ( row.hasClass( 'row' ) && !row.hasClass( '_row_template' ) ) {
					
					var weekday = row.find( '.weekday' );
					var start = row.find( '.start' );
					var end = row.find( '.end' );
					
					var weekdayVal = weekday.val();
					var startVal = parseFloat( opts.time_hash[ start.val() ] );
					var endVal = parseFloat( opts.time_hash[ end.val() ] );
					
					// check that start time is greater than end time
					if ( !( startVal < endVal ) ) {
						end.addClass( 'error_field' );
						errors += 'End time must be greater than start time.';
						return false;
					}
					
					// check range so that it is divisible by the given units
					if ( ( endVal - startVal ) % opts.unit ) {
						start.addClass( 'error_field' );
						end.addClass( 'error_field' );
						errors += 'Time period must be divisible by exactly ' + opts.unit + ' hr(s)';
						return false;								
					}
					
					// group together by weekday
					if ( !dailyGroups[ weekdayVal ] ) dailyGroups[ weekdayVal ] = [];
					dailyGroups[ weekdayVal ].push( row );
					
				}
				
			} );
			
			// check for overlaps
			$.each( dailyGroups, function( i, v ) {
				
				$.each( v, function( i2, v2 ) {
					
					var weekday1 = v2.find( '.weekday' );
					var start1 = v2.find( '.start' );
					var end1 = v2.find( '.end' );
					
					var startVal1 = parseFloat( opts.time_hash[ start1.val() ] );
					var endVal1 = parseFloat( opts.time_hash[ end1.val() ] );
					
					$.each( v, function( i3, v3 ) {

						var weekday2 = v3.find( '.weekday' );
						var start2 = v3.find( '.start' );
						var end2 = v3.find( '.end' );
						
						var startVal2 = parseFloat( opts.time_hash[ start2.val() ] );
						var endVal2 = parseFloat( opts.time_hash[ end2.val() ] );
						
						if ( weekday1.attr( 'name' ) != weekday2.attr( 'name' ) ) {
							
							if (
								( ( startVal2 >= startVal1 ) && ( startVal2 <= endVal1 ) ) || 
								( ( endVal2 >= startVal1 ) && ( endVal2 <= endVal1 ) ) || 
								( ( startVal1 >= startVal2 ) && ( startVal1 <= endVal2 ) ) || 
								( ( endVal1 >= startVal2 ) && ( endVal1 <= endVal2 ) )
							) {
								errors += 'There are overlapping time periods on ' + opts.weekday[ i ];
								
								weekday1.addClass( 'error_field' );
								start1.addClass( 'error_field' );
								end1.addClass( 'error_field' );
								
								weekday2.addClass( 'error_field' );
								start2.addClass( 'error_field' );
								end2.addClass( 'error_field' );											
							}
							
						}
						
						if ( errors ) return false;
						
					} );
					
					if ( errors ) return false;
					
				} );
				
				if ( errors ) return false;
				
			} );
			
			if ( errors ) {
				form.before( '<div class="error below-h2" id="bst_error" style="display: none;"><p>' + errors + '<\/p><\/div>' );
				formParent.find( '#bst_error' ).fadeIn( 300 );
				return false;
			} else {
				opts.get_row_tmpl_func().remove();
				return true;
			}
			
		} );
		
	};
	
	$.gekoWpBookingScheduleRowTmpl = null;
	
} )( jQuery );


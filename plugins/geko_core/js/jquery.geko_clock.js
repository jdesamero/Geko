;( function ( $ ) {
	
	$.fn.gekoClock = function( options ) {
		
		var opts = $.extend( {
			colon_sel: '.colon',
			weekday_sel: '.weekday',
			day_sel: '.day',
			hour_sel: '.hour',
			minute_sel: '.minute',
			ampm_sel: '.ampm'
		}, options );
		
		var calClock = $( this );					// calendar clock
		
		//// clock stuff
		
		//
		var colonOn = function () {
			setTimeout(function () {
				calClock.find( opts.colon_sel ).css( 'visibility', 'visible' );
				
				// update the time
				var now = new Date();
				
				calClock.find( opts.weekday_sel ).html( now.format( 'dddd' ) );
				calClock.find( opts.day_sel ).html( now.format( 'd' ) );
				calClock.find( opts.hour_sel ).html( now.format( 'h' ) );
				calClock.find( opts.minute_sel ).html( now.format( 'MM' ) );
				calClock.find( opts.ampm_sel ).html( now.format( 'TT' ) );
				
				colonOff();
			}, 100 );
		}
	
		var colonOff = function () {
			setTimeout( function () {
				calClock.find( opts.colon_sel ).css( 'visibility', 'hidden' );
				colonOn();
			}, 900 );
		}
		
		colonOn();
		
		
		return this;	
	};
	
} )( jQuery );

;( function ( $ ) {
	
	$.fn.gekoDefaultValue = function() {
		
		// function( options )
		// var opts = $.extend({}, options);
		
		$( this ).each( function() {
			$( this ).data( 'default', $( this ).val() );
			$( this ).focus( function() {
				if ( $( this ).data( 'default' ) == $( this ).val() ) $( this ).val( '' );
			} ).blur( function() {
				if ( !$( this ).val() ) $( this ).val( $( this ).data( 'default' ) );
			} );
		} );
		
		return this;
	};
	
} )( jQuery );

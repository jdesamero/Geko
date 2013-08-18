;( function ( $ ) {
	
	// update function
	$.gekoUpdate = function( state ) {
		$( state._update.selector ).trigger( state._update.event, state );
	};
	
	// 
	$.gekoStateFactory = function( state ) {
		
		return $.extend( {
			_states: {},
			_update: {
				selector: '.updateable',
				event: 'update'
			}
		}, state );
		
	};
	
} )( jQuery );


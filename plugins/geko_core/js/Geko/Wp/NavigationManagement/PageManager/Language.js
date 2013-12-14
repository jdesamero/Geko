;( function ( $ ) {
		
	$.gekoNavigationPageManager.registerPlugin({
		
		name: 'Geko_Wp_NavigationManagement_PageManager_Language',
		
		depends: 'Geko_Navigation_PageManager_ImplicitLabelAbstract',
		
		setup_li: function( li ) {
			
			//
			var mgmt = this;
			
			//
			li.bind( 'update', function( evt ) {
				
				var nav_params = $( this ).data( 'nav_params' );
				
				if ( mgmt.type == nav_params.type ) {
					$( this ).find( 'span.item_title a' ).html( 'Language Toggle' );				
				}
				
			} );
			
		}
		
	} );
	
} )( jQuery );
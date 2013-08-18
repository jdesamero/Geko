;( function ( $ ) {
		
	$.gekoNavigationPageManager.registerPlugin( {
		
		name: 'Geko_Wp_NavigationManagement_PageManager_Home',
		
		depends: 'Geko_Navigation_PageManager_ImplicitLabelAbstract',
		
		setup_li: function( li ) {
			
			//
			var mgmt = this;
			
			//
			li.bind( 'update', function( evt ) {
				
				var nav_params = $( this ).data( 'nav_params' );
				
				if ( mgmt.type == nav_params.type ) {
					$( this ).find( 'a.link' ).attr( 'href', mgmt.homepage_url );
					$( this ).find( 'span.item_title a').html(
						nav_params.label.htmlEntities() || 
						mgmt.homepage_title.htmlEntities()
					);
				}
				
			} );
			
		}
		
	} );
	
} )( jQuery );
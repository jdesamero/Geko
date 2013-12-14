;( function ( $ ) {
		
	$.gekoNavigationPageManager.registerPlugin({
		
		name: 'Geko_Navigation_PageManager_Custom',
		
		depends: 'Geko_Navigation_PageManager_ImplicitLabelAbstract',
		
		setup_li: function( li ) {
			
			//
			var mgmt = this;
			var navDlg = this.__elems.navDlg;
			
			var custom_subject = '#' + this.pfx_type + 'custom_subject';
			var custom_params = '#' + this.pfx_type + 'custom_params';
			
			//
			li.bind( 'pre_update', function( evt ) {
				
				var nav_params = $( this ).data( 'nav_params' );
				
				if ( mgmt.type == nav_params.type ) {
					nav_params.custom_subject = navDlg.find( custom_subject ).val();
					nav_params.custom_params = navDlg.find( custom_params ).val();
				}
				
			} );
			
			//
			li.bind( 'update', function( evt ) {
				
				var nav_params = $( this ).data( 'nav_params' );
				
				if ( mgmt.type == nav_params.type ) {
					
					$( this ).find( 'span.item_title a' ).html(
						nav_params.label || 
						nav_params.custom_subject
					);
					
				}
				
			} );
			
		},
		
		setup: function() {
			
			var mgmt = this;
			var navDlg = this.__elems.navDlg;
			
			var custom_subject = '#' + this.pfx_type + 'custom_subject';
			var custom_params = '#' + this.pfx_type + 'custom_params';
			
			//
			navDlg.bind( 'reset', function( evt ) {
				
				var nav_params = $( this ).data( 'selected_li' ).data( 'nav_params' );
				
				$( this ).find( custom_subject ).selValue( nav_params.custom_subject );
				$( this ).find( custom_params ).selValue( nav_params.custom_params );
				
			} );
				
		}
		
	} );
	
} )( jQuery );
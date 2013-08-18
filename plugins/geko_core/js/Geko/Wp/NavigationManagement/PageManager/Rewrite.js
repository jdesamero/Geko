;( function ( $ ) {
		
	$.gekoNavigationPageManager.registerPlugin( {
		
		name: 'Geko_Wp_NavigationManagement_PageManager_Rewrite',
		
		depends: 'Geko_Navigation_PageManager_ImplicitLabelAbstract',
		
		setup_li: function( li ) {
			
			//
			var mgmt = this;
			var navDlg = this.__elems.navDlg;
			
			var rw_subj = '#' + this.pfx_type + 'rw_subj';
			var rw_type = '#' + this.pfx_type + 'rw_type';
			var rw_cmthd = '#' + this.pfx_type + 'rw_cmthd';
			
			//
			li.bind( 'pre_update', function( evt ) {
				
				var nav_params = $( this ).data( 'nav_params' );
				
				if ( mgmt.type == nav_params.type ) {
					nav_params.rw_subj = navDlg.find( rw_subj ).val();
					nav_params.rw_type = navDlg.find( rw_type ).val();
					nav_params.rw_cmthd = navDlg.find( rw_cmthd ).val();
				}
				
			} );
			
			//
			li.bind( 'update', function( evt ) {
				
				var nav_params = $( this ).data( 'nav_params' );
				
				if ( mgmt.type == nav_params.type ) {
					
					$( this ).find( 'span.item_title a' ).html(
						nav_params.label.htmlEntities() || 
						nav_params.rw_subj.htmlEntities()
					);
					
				}
				
			} );
			
		},
		
		setup: function() {
			
			var mgmt = this;
			var navDlg = this.__elems.navDlg;
			
			var rw_subj = '#' + this.pfx_type + 'rw_subj';
			var rw_type = '#' + this.pfx_type + 'rw_type';
			var rw_cmthd = '#' + this.pfx_type + 'rw_cmthd';
			
			//
			$( '#' + this.pfx_type + 'rw_type' ).change( function() {
				
				var nav_params = navDlg.data( 'selected_li' ).data( 'nav_params' );
				
				nav_params.rw_type = $( this ).val();
				
				// trigger open event on the dialog
				navDlg.trigger( 'open' );
				
			} );
			
			//
			navDlg.bind( 'open', function( evt ) {
				
				var selectedLi = $( this ).data( 'selected_li' );
				var nav_params = selectedLi.data( 'nav_params' );
				
				if ( mgmt.type == nav_params.type ) {
					
					// only show rw_cmthd field if rw_type is set to "custom_method"
					$( this ).find( '.opt-' + mgmt.type + ' label[for=' + mgmt.pfx_type + 'rw_cmthd]' ).css('display', ( ( 'custom_method' == nav_params.rw_type ) ? '' : 'none' ) );
					$( this ).find( rw_cmthd ).css( 'display', ( ( 'custom_method' == nav_params.rw_type ) ? '' : 'none' ) );
					
				}
				
			} );
			
			//
			navDlg.bind( 'reset', function( evt ) {
				
				var nav_params = $( this ).data( 'selected_li' ).data( 'nav_params' );
				
				$( this ).find( rw_subj ).selValue( nav_params.rw_subj );
				$( this ).find( rw_type ).selValue( nav_params.rw_type );
				$( this ).find( rw_cmthd ).selValue( nav_params.rw_cmthd );
				
			} );
						
		}

		
	} );
	
} )( jQuery );
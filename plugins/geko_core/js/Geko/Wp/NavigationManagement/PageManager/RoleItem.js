;( function ( $ ) {
	
	$.gekoNavigationPageManager.registerPlugin( {
		
		name: 'Geko_Wp_NavigationManagement_PageManager_RoleItem',
		
		depends: 'Geko_Navigation_PageManager_ImplicitLabelAbstract',
		
		setup_li: function( li ) {
			
			//
			var mgmt = this;
			var navDlg = this.__elems.navDlg;
			var role_id = '#' + this.pfx_type + 'role_id';
			
			//
			li.bind( 'pre_update', function( evt ) {
				
				var nav_params = $( this ).data( 'nav_params' );
				
				if ( mgmt.type == nav_params.type ) {
					nav_params.role_id = navDlg.find( role_id ).val();
					nav_params.hide = true;
				}
				
			} );
			
			//
			li.bind( 'update', function( evt ) {
				
				var nav_params = $( this ).data( 'nav_params' );
				
				if ( mgmt.type == nav_params.type ) {
					if ( mgmt.role_params[ nav_params.role_id ] ) {
						
						$( this ).find( 'span.item_title a' ).html(
							nav_params.label.htmlEntities() || 
							mgmt.role_params[ nav_params.role_id ].title.htmlEntities()
						);
						
						$( this ).find( 'a.link' ).attr(
							'href',
							mgmt.role_params[ nav_params.role_id ].link
						);
					}
				}
				
			} );
			
		},
		
		setup: function() {
			
			var mgmt = this;
			var navDlg = this.__elems.navDlg;
			var role_id = '#' + this.pfx_type + 'role_id';
			var role_params = this.role_params;
			
			//
			$.each( this.role_groups, function( grp, items ) {
				var opts = '';
				
				$.each( items, function( i, r_id ) {
					var id_hint = '';
					var param = role_params[ r_id ];
					
					if ( !param.skip_id ) id_hint = ' (' + r_id + ')';
					
					opts += '<option value="' + r_id + '">' + param.title + id_hint + '</option>';
				} );
				
				opts = '<optgroup label="' + grp + '">' + opts + '</optgroup>';
				
				navDlg.find( role_id ).append( opts );
			} );
			
			
			
			//
			navDlg.bind( 'open', function( evt ) {
				
				var nav_params = $( this ).data( 'selected_li' ).data( 'nav_params' );
				
				if ( mgmt.type == nav_params.type ) {
					$( this ).find( '.opt-common' ).hide();
				}
				
			} );
			
			//
			navDlg.bind( 'reset', function( evt ) {
				
				var nav_params = $( this ).data( 'selected_li' ).data( 'nav_params' );
				
				$( this ).find( role_id ).selValue( nav_params.role_id );
				$( this ).find( '.opt-common' ).show();
				
			} );
			
			//
			navDlg.bind( 'type_change', function( evt ) {
				
				var nav_params = $( this ).data( 'selected_li' ).data( 'nav_params' );
				
				if ( mgmt.type == nav_params.type ) {
					$( this ).find( '.opt-common' ).hide();
				} else {
					$( this ).find( '.opt-common' ).show();				
				}
				
			} );
			
		}
		
	} );
	
} )( jQuery );
;( function ( $ ) {
	
	$.gekoNavigationPageManager.registerPlugin( {
		
		name: 'Geko_Wp_NavigationManagement_PageManager_Category',
		
		depends: 'Geko_Navigation_PageManager_ImplicitLabelAbstract',
		
		setup_li: function( li ) {
			
			//
			var mgmt = this;
			var navDlg = this.__elems.navDlg;
			var cat_id = '#' + this.pfx_type + 'cat_id';
			
			//
			li.bind( 'pre_update', function( evt ) {
				
				var nav_params = $( this ).data( 'nav_params' );
				
				if ( mgmt.type == nav_params.type ) {
					nav_params.cat_id = navDlg.find( cat_id ).val();
				}
				
			} );
			
			//
			li.bind( 'update', function( evt ) {
				
				var nav_params = $( this ).data( 'nav_params' );
				
				if ( mgmt.type == nav_params.type ) {
					if ( mgmt.cat_params[ nav_params.cat_id ] ) {
						
						$( this ).find( 'span.item_title a' ).html(
							nav_params.label.htmlEntities() || 
							mgmt.cat_params[ nav_params.cat_id ].title.htmlEntities()
						);
						
						$( this ).find( 'a.link' ).attr(
							'href',
							mgmt.cat_params[ nav_params.cat_id ].link
						);
					}
				}
				
			} );
			
		},
		
		init: function() {

			var mgmt = this;
			var navDlg = this.__elems.navDlg;
			var cat_id = '#' + this.pfx_type + 'cat_id';
			
			//
			if ( this.disable_params ) {
				navDlg.find( cat_id ).attr( 'disabled', 'disabled' ).css( 'color', 'gray' );
				navDlg.find( 'label[for=' + this.pfx_type + 'cat_id]' ).css( 'color', 'gray' );
			}
			
		},
		
		setup: function() {
			
			var mgmt = this;
			var navDlg = this.__elems.navDlg;
			var cat_id = '#' + this.pfx_type + 'cat_id';
			
			//
			$.each( this.cat_params, function(i, val) {
				navDlg.find( cat_id ).append(
					'<option value="' + i + '">' + val.title + ' (' + i + ')</option>'
				);
			} );
			
			//
			navDlg.bind( 'reset', function( evt ) {
				
				var nav_params = $( this ).data( 'selected_li' ).data( 'nav_params' );
				
				$( this ).find( cat_id ).selValue( nav_params.cat_id );
				
			} );
						
		}
		
	} );
	
} )( jQuery );
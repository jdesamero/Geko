;( function ( $ ) {
	
	$.gekoNavigationPageManager.registerPlugin( {
		
		name: 'Geko_Wp_NavigationManagement_PageManager_Author',
		
		depends: 'Geko_Navigation_PageManager_ImplicitLabelAbstract',
		
		setup_li: function( li ) {
			
			//
			var mgmt = this;
			var navDlg = this.__elems.navDlg;
			var author_id = '#' + this.pfx_type + 'author_id';
			
			//
			li.bind( 'pre_update', function( evt ) {
				
				var nav_params = $( this ).data( 'nav_params' );
				
				if ( mgmt.type == nav_params.type ) {
					nav_params.author_id = navDlg.find( author_id ).val();
				}
				
			} );
			
			//
			li.bind( 'update', function( evt ) {
				
				var nav_params = $( this ).data( 'nav_params' );
				
				if ( mgmt.type == nav_params.type ) {
					if ( mgmt.author_params[ nav_params.author_id ] ) {
						
						$( this ).find( 'span.item_title a' ).html(
							nav_params.label.htmlEntities() || 
							mgmt.author_params[ nav_params.author_id ].title.htmlEntities()
						);
						
						$( this ).find( 'a.link' ).attr(
							'href',
							mgmt.author_params[ nav_params.author_id ].link
						);
					}
				}
				
			} );
			
		},
		
		init: function() {

			var mgmt = this;
			var navDlg = this.__elems.navDlg;
			var author_id = '#' + this.pfx_type + 'author_id';
			
			//
			if ( this.disable_params ) {
				navDlg.find( author_id ).attr( 'disabled', 'disabled' ).css( 'color', 'gray' );
				navDlg.find( 'label[for=' + this.pfx_type + 'author_id]' ).css( 'color', 'gray' );
			}
			
		},
		
		setup: function() {
			
			var mgmt = this;
			var navDlg = this.__elems.navDlg;
			var author_id = '#' + this.pfx_type + 'author_id';
			
			//
			$.each( this.author_params, function(i, val) {
				navDlg.find( author_id ).append(
					'<option value="' + i + '">' + val.title + ' (' + i + ')</option>'
				);
			} );
			
			//
			navDlg.bind( 'reset', function( evt ) {
				
				var nav_params = $( this ).data( 'selected_li' ).data( 'nav_params' );
				
				$( this ).find( author_id ).selValue( nav_params.author_id );
				
			} );
						
		}
		
	} );
	
} )( jQuery );
;( function ( $ ) {
	
	$.gekoNavigationPageManager.registerPlugin( {
		
		name: 'Geko_Wp_NavigationManagement_PageManager_Post',
		
		depends: 'Geko_Navigation_PageManager_ImplicitLabelAbstract',
		
		setup_li: function( li ) {
			
			//
			var mgmt = this;
			var navDlg = this.__elems.navDlg;
			
			var post_type_id = '#' + this.pfx_type + 'post_type_id';
			var cat_id = '#' + this.pfx_type + 'cat_id';
			var author_id = '#' + this.pfx_type + 'author_id';
			
			
			//
			li.bind( 'pre_update', function( evt ) {
				
				var nav_params = $( this ).data( 'nav_params' );
				
				if ( mgmt.type == nav_params.type ) {
					
					nav_params.post_type_id = navDlg.find( post_type_id ).val();
					nav_params.cat_id = navDlg.find( cat_id ).val();
					nav_params.author_id = navDlg.find( author_id ).val();
					
					nav_params.hide = true;
				}
				
			} );
			
			//
			li.bind( 'update', function( evt ) {
				
				var nav_params = $( this ).data( 'nav_params' );
				
				if ( mgmt.type == nav_params.type ) {
					
					var post_type = mgmt.post_types[ nav_params.post_type_id ].slug;
					
					if ( 'category' == post_type ) {
						
						if ( mgmt.cat_params[ nav_params.cat_id ] ) {
							$( this ).find( 'span.item_title a' ).html(
								nav_params.label.htmlEntities() || 
								mgmt.cat_params[ nav_params.cat_id ].title.htmlEntities()
							);
						}
						
					} else if ( 'author' == post_type ) {
						
						if ( mgmt.author_params[ nav_params.author_id ] ) {
							$( this ).find( 'span.item_title a' ).html(
								nav_params.label || 
								mgmt.author_params[ nav_params.author_id ].title
							);
						}
						
					}
					
					// TO DO: this should always be disabled since there is
					// nothing to link to
					// $( this ).find( 'a.link' ).attr( ... );
					
				}
				
			} );
			
		},
		
		init: function() {

			var mgmt = this;
			var navDlg = this.__elems.navDlg;
			
			var post_type_id = '#' + this.pfx_type + 'post_type_id';
			var cat_id = '#' + this.pfx_type + 'cat_id';
			var author_id = '#' + this.pfx_type + 'author_id';
			
			//
			if ( this.disable_params ) {
				
				navDlg.find( post_type_id ).attr( 'disabled', 'disabled' ).css( 'color', 'gray' );
				navDlg.find( 'label[for=' + this.pfx_type + 'post_type_id]' ).css( 'color', 'gray' );
				
				navDlg.find( cat_id ).attr( 'disabled', 'disabled' ).css( 'color', 'gray' );
				navDlg.find( 'label[for=' + this.pfx_type + 'cat_id]' ).css( 'color', 'gray' );
				
				navDlg.find( author_id ).attr( 'disabled', 'disabled' ).css( 'color', 'gray' );
				navDlg.find( 'label[for=' + this.pfx_type + 'author_id]' ).css( 'color', 'gray' );
				
			}
			
		},
		
		setup: function() {
			
			var mgmt = this;
			var navDlg = this.__elems.navDlg;
			
			var post_type_id = '#' + this.pfx_type + 'post_type_id';
			var cat_id = '#' + this.pfx_type + 'cat_id';
			var author_id = '#' + this.pfx_type + 'author_id';
			
			
			//// populate fields
			
			//
			$.each( this.post_types, function(i, val) {
				navDlg.find( post_type_id ).append(
					'<option value="' + i + '">' + val.title + '</option>'
				);
			} );
			
			//
			$.each( this.cat_params, function(i, val) {
				navDlg.find( cat_id ).append(
					'<option value="' + i + '">' + val.title + ' (' + i + ')</option>'
				);
			} );
			
			//
			$.each( this.author_params, function(i, val) {
				navDlg.find( author_id ).append(
					'<option value="' + i + '">' + val.title + ' (' + i + ')</option>'
				);
			} );
			
			//
			$( post_type_id ).change( function() {
				
				var nav_params = navDlg.data( 'selected_li' ).data( 'nav_params' );
				
				nav_params.post_type_id = $( this ).val();
				
				// trigger open event on the dialog
				navDlg.trigger( 'open' );
				
			} );
			
			
			
			//// add functionality
			
			//
			navDlg.bind( 'open', function( evt ) {
				
				var nav_params = $( this ).data( 'selected_li' ).data( 'nav_params' );
				
				if ( mgmt.type == nav_params.type ) {
					
					$( this ).find( '.opt-common' ).hide();
					
					// show the appropriate options
					var post_type = mgmt.post_types[ nav_params.post_type_id ].slug;
					
					$( this ).find( '.opt-' + mgmt.type + ' label[for=' + mgmt.pfx_type + 'cat_id]' ).css( 'display', ( ( 'category' == post_type ) ? '' : 'none' ) );
					$( this ).find( cat_id ).css( 'display', ( ( 'category' == post_type ) ? '' : 'none' ) );
					
					$( this ).find( '.opt-' + mgmt.type + ' label[for=' + mgmt.pfx_type + 'author_id]' ).css( 'display', ( ( 'author' == post_type ) ? '' : 'none' ) );
					$( this ).find( author_id ).css( 'display', ( ( 'author' == post_type ) ? '' : 'none' ) );
					
				}
								
			} );
			
			//
			navDlg.bind( 'reset', function( evt ) {
				
				var nav_params = $( this ).data( 'selected_li' ).data( 'nav_params' );
				
				$( this ).find( post_type_id ).selValue( nav_params.post_type_id );
				$( this ).find( cat_id ).selValue( nav_params.cat_id );
				$( this ).find( author_id ).selValue( nav_params.author_id );
				
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
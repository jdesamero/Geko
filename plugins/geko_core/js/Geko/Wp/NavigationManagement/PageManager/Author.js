/*
 * "geko_core/js/Geko/Wp/NavigationManagement/PageManager/Author.js"
 * https://github.com/jdesamero/Geko
 *
 * Copyright (c) 2013 Joel Desamero.
 * Licensed under the MIT license.
 */

;( function ( $ ) {
	
	$.gekoNavigationPageManager.registerPlugin( {
		
		name: 'Geko_Wp_NavigationManagement_PageManager_Author',
		
		depends: 'Geko_Navigation_PageManager_ImplicitLabelAbstract',
		
		setup_li: function( eNavLi ) {
			
			//
			var _this = this;
			
			var eNavDlg = this.__elems.navDlg;
			var sTypePfx = this.pfx_type;
			var author_id = '#%sauthor_id'.printf( sTypePfx );
			
			//
			eNavLi.on( 'pre_update', function( evt ) {
				
				var eLi = $( this );
				
				var oNavParams = eLi.data( 'nav_params' );
				
				if ( _this.type == oNavParams.type ) {
					oNavParams.author_id = eNavDlg.find( author_id ).val();
				}
				
			} );
			
			//
			eNavLi.on( 'update', function( evt ) {
				
				var eLi = $( this );
				
				var oNavParams = eLi.data( 'nav_params' );
				
				if ( _this.type == oNavParams.type ) {
					if ( _this.author_params[ oNavParams.author_id ] ) {
						
						eLi.find( 'span.item_title a' ).html(
							oNavParams.label.htmlEntities() || 
							_this.author_params[ oNavParams.author_id ].title.htmlEntities()
						);
						
						eLi.find( 'a.link' ).attr(
							'href',
							_this.author_params[ oNavParams.author_id ].link
						);
					}
				}
				
			} );
			
		},
		
		init: function() {

			var _this = this;
			var eNavDlg = this.__elems.navDlg;
			var sTypePfx = this.pfx_type;
			var author_id = '#%sauthor_id'.printf( sTypePfx );
			
			//
			if ( this.disable_params ) {
				eNavDlg.find( author_id ).attr( 'disabled', 'disabled' ).css( 'color', 'gray' );
				eNavDlg.find( 'label[for=%sauthor_id]'.printf( sTypePfx ) ).css( 'color', 'gray' );
			}
			
		},
		
		setup: function() {
			
			var _this = this;
			
			var eNavDlg = this.__elems.navDlg;
			var sTypePfx = this.pfx_type;
			var author_id = '#%sauthor_id'.printf( sTypePfx );
			
			//
			$.each( this.author_params, function( i, val ) {
				eNavDlg.find( author_id ).append(
					'<option value="%d">%s (%d)</option>'.printf( i, val.title, i )
				);
			} );
			
			//
			eNavDlg.on( 'reset', function( evt ) {
				
				var eDlg = $( this );
				
				var oNavParams = eDlg.data( 'selected_li' ).data( 'nav_params' );
				
				eDlg.find( author_id ).selValue( oNavParams.author_id );
				
			} );
						
		}
		
	} );
	
} )( jQuery );
/*
 * "geko_core/js/Geko/Wp/NavigationManagement/PageManager/Page.js"
 * https://github.com/jdesamero/Geko
 *
 * Copyright (c) 2013 Joel Desamero.
 * Licensed under the MIT license.
 */

;( function ( $ ) {
	
	$.gekoNavigationPageManager.registerPlugin( {
		
		name: 'Geko_Wp_NavigationManagement_PageManager_Page',
		
		depends: 'Geko_Navigation_PageManager_ImplicitLabelAbstract',
		
		setup_li: function( eNavLi ) {
			
			//
			var _this = this;
			
			var oPageParams = _this.page_params;
			
			var eNavDlg = this.__elems.navDlg;
			var sNavType = this.type;
			var sTypePfx = this.pfx_type;
			var sPageDrpdwnSel = '#%spage_id'.printf( sTypePfx );
			
			//
			eNavLi.on( 'pre_update', function( evt ) {
				
				var eLi = $( this );
				
				var oNavParams = eLi.data( 'nav_params' );
				var sCurNavType = oNavParams.type;
				
				if ( sNavType == sCurNavType ) {
					oNavParams[ 'page_id' ] = eNavDlg.find( sPageDrpdwnSel ).val();
				}
				
			} );
			
			//
			eNavLi.on( 'update', function( evt ) {
				
				var eLi = $( this );
				
				var oNavParams = eLi.data( 'nav_params' );
				var sCurNavType = oNavParams.type;
				
				if ( sNavType == sCurNavType ) {
					
					var iCurPageId = oNavParams.page_id;
					var oCurPage = oPageParams[ iCurPageId ];
					
					if ( oCurPage ) {
						
						var sPageTitle = oNavParams.label.htmlEntities() || oCurPage.title.htmlEntities();
						
						eLi.find( 'span.item_title a' ).html( sPageTitle );
						
						eLi.find( 'a.link' ).attr( 'href', oCurPage.link );
					}
				}
				
			} );
						
		},
		
		init: function() {
			
			var _this = this;
			
			var eNavDlg = this.__elems.navDlg;
			var sTypePfx = this.pfx_type;
			
			//
			if ( this.disable_params ) {
				
				var sPageDrpdwnSel = '#%spage_id'.printf( sTypePfx );
				eNavDlg.find( sPageDrpdwnSel ).attr( 'disabled', 'disabled' ).css( 'color', 'gray' );
				
				var sPageLabelSel = 'label[for=%spage_id]'.printf( sTypePfx );
				eNavDlg.find( sPageLabelSel ).css( 'color', 'gray' );
			}
			
		},
		
		setup: function() {
			
			var _this = this;
			
			var eNavDlg = this.__elems.navDlg;
			var sTypePfx = this.pfx_type;
			var sPageDrpdwnSel = '#%spage_id'.printf( sTypePfx );
			
			//
			$.each( this.page_params, function( i, val ) {
				eNavDlg.find( sPageDrpdwnSel ).append(
					'<option value="%d">%s (%d)</option>'.printf( i, val.title, i )
				);
			} );
			
			//
			eNavDlg.on( 'reset', function( evt ) {
				
				var eDlg = $( this );
				
				var oNavParams = eDlg.data( 'selected_li' ).data( 'nav_params' );
				var iCurPageId = oNavParams.page_id;
				
				eDlg.find( sPageDrpdwnSel ).selValue( iCurPageId );
				
			} );
						
		}
		
	} );
	
} )( jQuery );
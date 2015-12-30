/*
 * "geko_core/js/Geko/Wp/NavigationManagement/PageManager/Language.js"
 * https://github.com/jdesamero/Geko
 *
 * Copyright (c) 2013 Joel Desamero.
 * Licensed under the MIT license.
 */

;( function ( $ ) {
		
	$.gekoNavigationPageManager.registerPlugin({
		
		name: 'Geko_Wp_NavigationManagement_PageManager_Language',
		
		depends: 'Geko_Navigation_PageManager_ImplicitLabelAbstract',
		
		setup_li: function( eNavLi ) {
			
			//
			var _this = this;
			
			var oLangParams = this.lang_params;
			
			var eNavDlg = this.__elems.navDlg;
			var sNavType = this.type;
			var sTypePfx = this.pfx_type;
			var sLangDrpdwnSel = '#%slang'.printf( sTypePfx );
			
			
			
			
			// This should always be disabled since there is nothing to link to
			eNavLi.find( 'a.link' ).on( 'click', function() {
				
				var oNavParams = eNavLi.data( 'nav_params' );
				var sCurNavType = oNavParams.type;
				
				if ( sNavType == sCurNavType ) {
					return false;
				}
				
			} );

			
			//
			eNavLi.on( 'pre_update', function( evt ) {
				
				var eLi = $( this );
				
				var oNavParams = eLi.data( 'nav_params' );
				var sCurNavType = oNavParams.type;
				
				if ( sNavType == sCurNavType ) {
					oNavParams[ 'lang' ] = eNavDlg.find( sLangDrpdwnSel ).val();
				}
				
			} );
			
			
			//
			eNavLi.on( 'update', function( evt ) {
				
				var eLi = $( this );
				
				var oNavParams = eLi.data( 'nav_params' );
				var sCurNavType = oNavParams.type;
				
				if ( sNavType == sCurNavType ) {

					var sCurLang = oNavParams.lang;
					var sCurLangTitle = oLangParams[ sCurLang ];
					
					if ( sCurLangTitle ) {
						
						var sLangTitle = oNavParams.label.htmlEntities() || sCurLangTitle;
						
						eLi.find( 'span.item_title a' ).html( sLangTitle );
					}
				}
				
			} );
			
		},
		
		setup: function() {
			
			var _this = this;
			var eNavDlg = this.__elems.navDlg;
			var sNavType = this.type;
			var sTypePfx = this.pfx_type;
			
			
			var sLangDrpdwnSel = '#%slang'.printf( sTypePfx );
			
			var oLangParams = this.lang_params;
			
			var eLangDrpdwn = eNavDlg.find( sLangDrpdwnSel );
			
			$.each( oLangParams, function( k, v ) {
				eLangDrpdwn.append(
					'<option value="%s">%s</option>'.printf( k, v )
				);
			} );
			
			
			
			//// add dialog triggers

			//
			eNavDlg.on( 'reset', function( evt ) {
								
				var eDlg = $( this );
				
				var oNavParams = eDlg.data( 'selected_li' ).data( 'nav_params' );
				
				var sCurLang = oNavParams.lang;
				eLangDrpdwn.val( sCurLang );
				
			} );

			
		}

		
	} );
	
} )( jQuery );
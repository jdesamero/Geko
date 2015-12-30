/*
 * "geko_core/js/Geko/Wp/NavigationManagement/PageManager/CustomType.js"
 * https://github.com/jdesamero/Geko
 *
 * Copyright (c) 2013 Joel Desamero.
 * Licensed under the MIT license.
 */

;( function ( $ ) {
	
	$.gekoNavigationPageManager.registerPlugin( {
		
		name: 'Geko_Wp_NavigationManagement_PageManager_CustomType',
		
		depends: 'Geko_Navigation_PageManager_ImplicitLabelAbstract',
		
		setup_li: function( eNavLi ) {
			
			//
			var _this = this;
			
			var oCptParams = this.cpt_params;
			
			var eNavDlg = this.__elems.navDlg;
			var sNavType = this.type;
			var sTypePfx = this.pfx_type;
			var sCptDrpdwnSel = '#%scustom_post_type'.printf( sTypePfx );
			
			//
			eNavLi.on( 'pre_update', function( evt ) {
				
				var eLi = $( this );
				
				var oNavParams = eLi.data( 'nav_params' );
				var sCurNavType = oNavParams.type;
				
				if ( sNavType == sCurNavType ) {
					oNavParams[ 'custom_post_type' ] = eNavDlg.find( sCptDrpdwnSel ).val();
				}
				
			} );
			
			//
			eNavLi.on( 'update', function( evt ) {
				
				var eLi = $( this );
				
				var oNavParams = eLi.data( 'nav_params' );
				var sCurNavType = oNavParams.type;

				if ( sNavType == sCurNavType ) {
					
					var sCurCpt = oNavParams.custom_post_type;
					var oCurCpt = oCptParams[ sCurCpt ];
					
					if ( oCurCpt ) {
						
						var sCptTitle = oNavParams.label.htmlEntities() || oCurCpt.label.htmlEntities();
						
						eLi.find( 'span.item_title a' ).html( sCptTitle );
						eLi.find( 'a.link' ).attr( 'href', oCurCpt.link );
					}					
				}
				
			} );
			
		},
		
		init: function() {
			
			var eNavDlg = this.__elems.navDlg;
			var sTypePfx = this.pfx_type;
			
			//
			if ( this.disable_params ) {
				
				var sCptDrpdwnSel = '#%scustom_post_type'.printf( sTypePfx );
				var sCptLabelSel = 'label[for=%scustom_post_type]'.printf( sTypePfx );
				
				eNavDlg.find( sCptDrpdwnSel ).attr( 'disabled', 'disabled' ).css( 'color', 'gray' );
				eNavDlg.find( sCptLabelSel ).css( 'color', 'gray' );
				
			}
			
		},
		
		setup: function() {
			
			var _this = this;
			var eNavDlg = this.__elems.navDlg;
			var sNavType = this.type;
			var sTypePfx = this.pfx_type;
			
			
			var sCptDrpdwnSel = '#%scustom_post_type'.printf( sTypePfx );
			
			var oCptParams = this.cpt_params;
			
			var eCptDrpdwn = eNavDlg.find( sCptDrpdwnSel );
			
			
			$.each( oCptParams, function( k, v ) {
				eCptDrpdwn.append(
					'<option value="%s">%s</option>'.printf( k, v.label )
				);
			} );
			
			
			//// add dialog triggers

			//
			eNavDlg.on( 'reset', function( evt ) {
								
				var eDlg = $( this );
				
				var oNavParams = eDlg.data( 'selected_li' ).data( 'nav_params' );
				
				var sCurCpt = oNavParams.custom_post_type;
				eCptDrpdwn.val( sCurCpt );
				
			} );

			
		}
		
	} );
	
} )( jQuery );
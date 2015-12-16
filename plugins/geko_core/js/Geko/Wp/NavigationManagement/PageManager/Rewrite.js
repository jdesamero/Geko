/*
 * "geko_core/js/Geko/Wp/NavigationManagement/PageManager/Rewrite.js"
 * https://github.com/jdesamero/Geko
 *
 * Copyright (c) 2013 Joel Desamero.
 * Licensed under the MIT license.
 */

;( function ( $ ) {
		
	$.gekoNavigationPageManager.registerPlugin( {
		
		name: 'Geko_Wp_NavigationManagement_PageManager_Rewrite',
		
		depends: 'Geko_Navigation_PageManager_ImplicitLabelAbstract',
		
		setup_li: function( eNavLi ) {
			
			//
			var _this = this;
			
			var eNavDlg = this.__elems.navDlg;
			var sNavType = this.type;
			var sTypePfx = this.pfx_type;
			
			var sRwSubjTxtSel = '#%srw_subj'.printf( sTypePfx );
			var sRwTypeDrpdwnSel = '#%srw_type'.printf( sTypePfx );
			var sRwCmthdTxtSel = '#%srw_cmthd'.printf( sTypePfx );
			
			//
			eNavLi.on( 'pre_update', function( evt ) {
				
				var eLi = $( this );
				
				var oNavParams = eLi.data( 'nav_params' );
				var sCurNavType = oNavParams.type;
				
				if ( sNavType == sCurNavType ) {
					oNavParams[ 'rw_subj' ] = eNavDlg.find( sRwSubjTxtSel ).val();
					oNavParams[ 'rw_type' ] = eNavDlg.find( sRwTypeDrpdwnSel ).val();
					oNavParams[ 'rw_cmthd' ] = eNavDlg.find( sRwCmthdTxtSel ).val();
				}
				
			} );
			
			//
			eNavLi.on( 'update', function( evt ) {
				
				var eLi = $( this );
				
				var oNavParams = eLi.data( 'nav_params' );
				var sCurNavType = oNavParams.type;
				
				if ( sNavType == sCurNavType ) {
					
					eLi.find( 'span.item_title a' ).html(
						oNavParams.label.htmlEntities() || 
						oNavParams.rw_subj.htmlEntities()
					);
					
				}
				
			} );
			
		},
		
		setup: function() {
			
			var _this = this;
			
			var eNavDlg = this.__elems.navDlg;
			var sNavType = this.type;
			var sTypePfx = this.pfx_type;
			
			var sRwSubjTxtSel = '#%srw_subj'.printf( sTypePfx );
			var sRwTypeDrpdwnSel = '#%srw_type'.printf( sTypePfx );
			var sRwCmthdTxtSel = '#%srw_cmthd'.printf( sTypePfx );
			
			var sCustMthdLabelSel = '.opt-%s label[for=%srw_cmthd]'.printf( sNavType, sTypePfx );
			
			
			
			//
			$( sRwTypeDrpdwnSel ).change( function() {
				
				var eDrpdwn = $( this );
				
				var oNavParams = eNavDlg.data( 'selected_li' ).data( 'nav_params' );
				
				oNavParams[ 'rw_type' ] = eDrpdwn.val();
				
				// trigger open event on the dialog
				eNavDlg.trigger( 'open' );
				
			} );
			
			//
			eNavDlg.on( 'open', function( evt ) {
				
				var eDlg = $( this );
				
				var eSelectedLi = eDlg.data( 'selected_li' );
				
				var oNavParams = eSelectedLi.data( 'nav_params' );
				var sCurNavType = oNavParams.type;
				
				if ( sNavType == sCurNavType ) {
					
					// only show rw_cmthd field if rw_type is set to "custom_method"
					var sRwTypeDisplayCss = ( 'custom_method' == oNavParams.rw_type ) ? '' : 'none' ;
					
					eDlg.find( sCustMthdLabelSel ).css( 'display', sRwTypeDisplayCss );
					eDlg.find( sRwCmthdTxtSel ).css( 'display', sRwTypeDisplayCss );
					
				}
				
			} );
			
			//
			eNavDlg.on( 'reset', function( evt ) {
				
				var eDlg = $( this );
				
				var oNavParams = eDlg.data( 'selected_li' ).data( 'nav_params' );
				
				eDlg.find( sRwSubjTxtSel ).selValue( oNavParams.rw_subj );
				eDlg.find( sRwTypeDrpdwnSel ).selValue( oNavParams.rw_type );
				eDlg.find( sRwCmthdTxtSel ).selValue( oNavParams.rw_cmthd );
				
			} );
						
		}

		
	} );
	
} )( jQuery );
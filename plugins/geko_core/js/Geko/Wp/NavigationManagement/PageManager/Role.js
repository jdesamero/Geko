/*
 * "geko_core/js/Geko/Wp/NavigationManagement/PageManager/Role.js"
 * https://github.com/jdesamero/Geko
 *
 * Copyright (c) 2013 Joel Desamero.
 * Licensed under the MIT license.
 */

;( function ( $ ) {
	
	$.gekoNavigationPageManager.registerPlugin( {
		
		name: 'Geko_Wp_NavigationManagement_PageManager_Role',
		
		depends: 'Geko_Navigation_PageManager_ImplicitLabelAbstract',
		
		setup_li: function( eNavLi ) {
			
			//
			var _this = this;
			
			var oRoleParams = this.role_params;
			
			var eNavDlg = this.__elems.navDlg;
			var sNavType = this.type;
			var sTypePfx = this.pfx_type;
			var sRoleDrpdwnSel = '#%srole_id'.printf( sTypePfx );
			
			//
			eNavLi.on( 'pre_update', function( evt ) {
				
				var eLi = $( this );
				
				var oNavParams = eLi.data( 'nav_params' );
				
				if ( sNavType == oNavParams.type ) {
					oNavParams[ 'role_id' ] = eNavDlg.find( sRoleDrpdwnSel ).val();
				}
				
			} );
			
			//
			eNavLi.on( 'update', function( evt ) {
				
				var eLi = $( this );
				
				var oNavParams = eLi.data( 'nav_params' );
				
				if ( sNavType == oNavParams.type ) {
					
					var iCurRoleId = oNavParams.role_id;
					var oCurRole = oRoleParams[ iCurRoleId ];
					
					if ( oCurRole ) {
						
						var sRoleItem = oNavParams.label.htmlEntities() || oCurRole.title.htmlEntities();
						
						eLi.find( 'span.item_title a' ).html( sRoleItem );
						
						eLi.find( 'a.link' ).attr( 'href', oCurRole.link );
					}
				}
				
			} );
			
		},
		
		setup: function() {
			
			//
			var _this = this;

			var oRoleParams = this.role_params;
			var oRoleGroups = this.role_groups;
			
			var eNavDlg = this.__elems.navDlg;
			var sTypePfx = this.pfx_type;
			var sRoleDrpdwnSel = '#%srole_id'.printf( sTypePfx );
			
			//
			$.each( oRoleGroups, function( sGrp, aItems ) {
				
				var sOpts = '';
				
				$.each( aItems, function( i, iRoleId ) {
					
					var sIdHint = '';
					var oParam = oRoleParams[ iRoleId ];
					
					if ( !oParam.skip_id ) sIdHint = ' (%s)'.printf( iRoleId );
					
					sOpts += '<option value="%s">%s%s</option>'.printf( iRoleId, oParam.title, sIdHint );
				} );
				
				var sOptGrp = '<optgroup label="%s">%s</optgroup>'.printf( sGrp, sOpts );
				
				eNavDlg.find( sRoleDrpdwnSel ).append( sOptGrp );
			} );
			
			//
			eNavDlg.on( 'reset', function( evt ) {
				
				var eDlg = $( this );
				
				var oNavParams = eDlg.data( 'selected_li' ).data( 'nav_params' );
				var iCurRoleId = oNavParams.role_id;
				
				eDlg.find( sRoleDrpdwnSel ).val( iCurRoleId );
				
			} );
						
		}
		
	} );
	
} )( jQuery );
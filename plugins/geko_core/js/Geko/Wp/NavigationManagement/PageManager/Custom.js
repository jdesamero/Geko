;( function ( $ ) {
		
	$.gekoNavigationPageManager.registerPlugin({
		
		name: 'Geko_Wp_NavigationManagement_PageManager_Custom',
		
		depends: 'Geko_Navigation_PageManager_ImplicitLabelAbstract',
		
		setup_li: function( eNavLi ) {
			
			//
			var _this = this;
			
			var eNavDlg = this.__elems.navDlg;
			var sNavType = this.type;
			var sTypePfx = this.pfx_type;
			
			var sCustomSubjectSel = '#%scustom_subject'.printf( sTypePfx );
			var sCustomParamsSel = '#%scustom_params'.printf( sTypePfx );
			
			//
			eNavLi.on( 'pre_update', function( evt ) {
				
				var eLi = $( this );
				
				var oNavParams = eLi.data( 'nav_params' );
				var sCurNavType = oNavParams.type;
				
				if ( sNavType == sCurNavType ) {
					oNavParams[ 'custom_subject' ] = eNavDlg.find( sCustomSubjectSel ).val();
					oNavParams[ 'custom_params' ] = eNavDlg.find( sCustomParamsSel ).val();
				}
				
			} );
			
			//
			eNavLi.on( 'update', function( evt ) {
				
				var eLi = $( this );
				
				var oNavParams = eLi.data( 'nav_params' );
				var sCurNavType = oNavParams.type;
				
				if ( sNavType == sCurNavType ) {
					
					var sFullItemTitle = oNavParams.label || oNavParams.custom_subject ;
					
					eLi.find( 'span.item_title a' ).html( sFullItemTitle );
				}
				
			} );
			
		},
		
		setup: function() {
			
			var _this = this;
			
			var eNavDlg = this.__elems.navDlg;
			
			var sTypePfx = this.pfx_type;
			
			var sCustomSubjectSel = '#%scustom_subject'.printf( sTypePfx );
			var sCustomParamsSel = '#%scustom_params'.printf( sTypePfx );
			
			//
			eNavDlg.on( 'reset', function( evt ) {
				
				var eDlg = $( this );
				
				var oNavParams = eDlg.data( 'selected_li' ).data( 'nav_params' );
				
				eDlg.find( sCustomSubjectSel ).val( oNavParams.custom_subject );
				eDlg.find( sCustomParamsSel ).val( oNavParams.custom_params );
				
			} );
				
		}
		
	} );
	
} )( jQuery );
/*
 * "geko_core/js/Geko/Wp/NavigationManagement/PageManager/Category.js"
 * https://github.com/jdesamero/Geko
 *
 * Copyright (c) 2013 Joel Desamero.
 * Licensed under the MIT license.
 */

;( function ( $ ) {
	
	$.gekoNavigationPageManager.registerPlugin( {
		
		name: 'Geko_Wp_NavigationManagement_PageManager_Category',
		
		depends: 'Geko_Navigation_PageManager_ImplicitLabelAbstract',
		
		setup_li: function( eNavLi ) {
			
			//
			var _this = this;
			
			var oCatParams = this.cat_params;
			var oCatsNorm = oCatParams.cats_norm;
			var oCatTypes = oCatParams.cat_types;
			
			var eNavDlg = this.__elems.navDlg;
			var sNavType = this.type;
			var sTypePfx = this.pfx_type;
			var sCatsDrpdwnSel = '#%scat_id'.printf( sTypePfx );
			
			//
			eNavLi.on( 'pre_update', function( evt ) {
				
				var eLi = $( this );
				
				var oNavParams = eLi.data( 'nav_params' );
				var sCurNavType = oNavParams.type;
				
				if ( sNavType == sCurNavType ) {
					oNavParams[ 'cat_id' ] = eNavDlg.find( sCatsDrpdwnSel ).val();
				}
				
			} );
			
			//
			eNavLi.on( 'update', function( evt ) {
				
				var eLi = $( this );
				
				var oNavParams = eLi.data( 'nav_params' );
				var sCurNavType = oNavParams.type;
				
				if ( sNavType == sCurNavType ) {
					
					var iCurCatId = oNavParams.cat_id;
					var oCurCat = oCatsNorm[ iCurCatId ];
					
					if ( oCurCat ) {
						
						var sCatTitle = oNavParams.label.htmlEntities() || oCurCat.title.htmlEntities();
						var sCatSpanHtml = '%s (%s)'.printf( sCatTitle, oCatTypes[ oCurCat.type ] );
						
						eLi.find( 'span.item_title a' ).html( sCatSpanHtml );
						
						eLi.find( 'a.link' ).attr( 'href', oCurCat.link );
					}
				}
				
			} );
			
		},
		
		init: function() {
			
			var eNavDlg = this.__elems.navDlg;
			var sTypePfx = this.pfx_type;
			
			//
			if ( this.disable_params ) {
				
				var sCatsDrpdwnSel = '#%scat_id'.printf( sTypePfx );
				eNavDlg.find( sCatsDrpdwnSel ).attr( 'disabled', 'disabled' ).css( 'color', 'gray' );
				
				var sCatsLabelSel = 'label[for=%scat_id]'.printf( sTypePfx );
				eNavDlg.find( sCatsLabelSel ).css( 'color', 'gray' );
			}
			
		},
		
		setup: function() {
			
			var _this = this;
			var eNavDlg = this.__elems.navDlg;
			var sNavType = this.type;
			var sTypePfx = this.pfx_type;
			
			
			var sCatTypesDrpdwnSel = '#%scat_type'.printf( sTypePfx );
			var sCatsDrpdwnSel = '#%scat_id'.printf( sTypePfx );
			
			var oCatParams = _this.cat_params;
			var oCatTypes = oCatParams.cat_types;
			var oCatsNorm = oCatParams.cats_norm;
			
			var eCatTypesDrpdwn = eNavDlg.find( sCatTypesDrpdwnSel );
			var eCatsDrpdwn = eNavDlg.find( sCatsDrpdwnSel );
			
			$.each( oCatTypes, function( k, v ) {
				eCatTypesDrpdwn.append(
					'<option value="%s">%s</option>'.printf( k, v )
				);
			} );
						
			$.each( oCatsNorm, function( i, val ) {
				eCatsDrpdwn.append(
					'<option value="%d" class="type-%s">%s (%d)</option>'.printf( i, val.type, val.title, i )
				);
			} );

			eCatTypesDrpdwn.on( 'change', function() {
				
				// trigger open event on the dialog
				eNavDlg.trigger( 'open', [ true ] );
				
			} );
			
			
			
			
			//// add dialog triggers

			//
			eNavDlg.on( 'open', function( evt, bChangeType ) {
				
				var eDlg = $( this );
				
				var oNavParams = eDlg.data( 'selected_li' ).data( 'nav_params' );
				var sCurNavType = oNavParams.type;
				
				if ( sNavType == sCurNavType ) {
					
					var sCurCatType = eCatTypesDrpdwn.val();
					if ( sCurCatType ) {
						
						eCatsDrpdwn.showSelOpts( '.type-%s'.printf( sCurCatType ) );
						
						// select default sub-option if type was changed
						if ( bChangeType ) {
							eCatsDrpdwn.find( 'option:first-child' ).attr( 'selected', 'selected' );
						}
					}
					
				}
				
			} );

			
			//
			eNavDlg.on( 'reset', function( evt ) {
								
				var eDlg = $( this );
				
				
				var oNavParams = eDlg.data( 'selected_li' ).data( 'nav_params' );
				var iCurCatId = oNavParams.cat_id;
				
				eCatsDrpdwn.showSelOpts( '*' );				// get everyone first
				eCatsDrpdwn.selValue( iCurCatId );
				
				var eCurOpt = eCatsDrpdwn.find( ':selected' );
				
				if ( eCurOpt.length ) {
					var sCurCatType = eCurOpt.attr( 'class' ).replace( 'type-', '' );
					eCatTypesDrpdwn.selValue( sCurCatType );
				} else {
					// set default type
					eCatTypesDrpdwn.find( 'option:first-child' ).attr( 'selected', 'selected' );
				}
				
			} );
						
		}
		
	} );
	
} )( jQuery );
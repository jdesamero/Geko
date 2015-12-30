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
			
			var oPageParams = this.page_params;
			var oPagesNorm = oPageParams.pages_norm;
			var oPageTypes = oPageParams.page_types;
			var iPageTypesCount = oPageParams.page_types_count;
			
			
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
					var oCurPage = oPagesNorm[ iCurPageId ];
					
					if ( oCurPage ) {
						
						var sPageTitle = oNavParams.label.htmlEntities() || oCurPage.title.htmlEntities();
						
						// include page type in title
						if ( iPageTypesCount > 1 ) {
							sPageTitle = '%s (%s)'.printf( sPageTitle, oPageTypes[ oCurPage.type ] );
						}
						
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

				var sPageTypeDrpdwnSel = '#%spage_type'.printf( sTypePfx );
				var sPageTypeLabelSel = 'label[for=%spage_type]'.printf( sTypePfx );
				
				var sPageDrpdwnSel = '#%spage_id'.printf( sTypePfx );
				var sPageLabelSel = 'label[for=%spage_id]'.printf( sTypePfx );
				
				eNavDlg.find( sPageTypeDrpdwnSel ).attr( 'disabled', 'disabled' ).css( 'color', 'gray' );
				eNavDlg.find( sPageTypeLabelSel ).css( 'color', 'gray' );
				
				eNavDlg.find( sPageDrpdwnSel ).attr( 'disabled', 'disabled' ).css( 'color', 'gray' );
				eNavDlg.find( sPageLabelSel ).css( 'color', 'gray' );
			}
			
		},
		
		setup: function() {
			
			var _this = this;
			var eNavDlg = this.__elems.navDlg;
			var sNavType = this.type;
			var sTypePfx = this.pfx_type;
			
			
			var oPageParams = this.page_params;
			var oPagesNorm = oPageParams.pages_norm;
			var oPageTypes = oPageParams.page_types;
			var iPageTypesCount = oPageParams.page_types_count;
			
			var sPageTypeLabelSel = 'label[for=%spage_type]'.printf( sTypePfx );
			var sPageTypeDrpdwnSel = '#%spage_type'.printf( sTypePfx );
			
			var sPageDrpdwnSel = '#%spage_id'.printf( sTypePfx );
			
			
			var ePageTypeLabel = eNavDlg.find( sPageTypeLabelSel );
			var ePageTypeDrpdwn = eNavDlg.find( sPageTypeDrpdwnSel );
			
			var ePageDrpdwn = eNavDlg.find( sPageDrpdwnSel );
			

			//
			$.each( oPageTypes, function( k, v ) {
				ePageTypeDrpdwn.append(
					'<option value="%s">%s</option>'.printf( k, v )
				);
			} );
			
			//
			$.each( oPagesNorm, function( i, val ) {
				ePageDrpdwn.append(
					'<option value="%d" class="type-%s">%s (%d)</option>'.printf( i, val.type, val.title, i )
				);
			} );
			
			ePageTypeDrpdwn.on( 'change', function() {
				
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
					
					if ( iPageTypesCount <= 1 ) {
						ePageTypeLabel.hide();
						ePageTypeDrpdwn.hide();
					} else {
						// only show if more than one
						ePageTypeLabel.show();
						ePageTypeDrpdwn.show();
					}
					
					var sCurPageType = ePageTypeDrpdwn.val();
					if ( sCurPageType ) {
						
						ePageDrpdwn.showSelOpts( '.type-%s'.printf( sCurPageType ) );
						
						// select default sub-option if type was changed
						if ( bChangeType ) {
							ePageDrpdwn.find( 'option:first-child' ).attr( 'selected', 'selected' );
						}
					}
					
				}
				
			} );

			
			
			
			//
			eNavDlg.on( 'reset', function( evt ) {
				
				var eDlg = $( this );
				
				var oNavParams = eDlg.data( 'selected_li' ).data( 'nav_params' );
				var iCurPageId = oNavParams.page_id;
				
				ePageDrpdwn.showSelOpts( '*' );				// get everyone first
				ePageDrpdwn.val( iCurPageId );
				
				var eCurOpt = ePageDrpdwn.find( ':selected' );
				
				if ( eCurOpt.length ) {
					var sCurPageType = eCurOpt.attr( 'class' ).replace( 'type-', '' );
					ePageTypeDrpdwn.val( sCurPageType );
				} else {
					// set default type
					ePageTypeDrpdwn.find( 'option:first-child' ).attr( 'selected', 'selected' );
				}
				
			} );
						
		}
		
	} );
	
} )( jQuery );
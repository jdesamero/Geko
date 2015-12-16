/*
 * "geko_core/js/Geko/Wp/NavigationManagement/PageManager/Post.js"
 * https://github.com/jdesamero/Geko
 *
 * Copyright (c) 2013 Joel Desamero.
 * Licensed under the MIT license.
 */

;( function ( $ ) {
	
	$.gekoNavigationPageManager.registerPlugin( {
		
		name: 'Geko_Wp_NavigationManagement_PageManager_Post',
		
		depends: 'Geko_Navigation_PageManager_ImplicitLabelAbstract',
		
		setup_li: function( eNavLi ) {
			
			//
			var _this = this;
			
			var oPostTypes = this.post_types;
			
			var oCatParams = this.cat_params;
			var oCatTypes = oCatParams.cat_types;
			var oCatsNorm = oCatParams.cats_norm;
			
			var oAuthorParams = this.author_params;
			
			
			var eNavDlg = this.__elems.navDlg;
			var sNavType = this.type;
			var sTypePfx = this.pfx_type;
			
			var sPostTypeDrpdwnSel = '#%spost_type_id'.printf( sTypePfx );
			var sCatDrpdwnSel = '#%scat_id'.printf( sTypePfx );
			var sAuthorDrpdwnSel = '#%sauthor_id'.printf( sTypePfx );
			
			
			// This should always be disabled since there is nothing to link to
			eNavLi.find( 'a.link' ).on( 'click', function() {
				return false;
			} );
			
			
			//
			eNavLi.on( 'pre_update', function( evt ) {
				
				var eLi = $( this );
				
				var oNavParams = eLi.data( 'nav_params' );
				var sCurNavType = oNavParams.type;
				
				if ( sNavType == sCurNavType ) {
					
					oNavParams[ 'post_type_id' ] = eNavDlg.find( sPostTypeDrpdwnSel ).val();
					oNavParams[ 'cat_id' ] = eNavDlg.find( sCatDrpdwnSel ).val();
					oNavParams[ 'author_id' ] = eNavDlg.find( sAuthorDrpdwnSel ).val();
					
					oNavParams[ 'hide' ] = true;
				}
				
			} );
			
			//
			eNavLi.on( 'update', function( evt ) {
				
				var eLi = $( this );
				
				var oNavParams = eLi.data( 'nav_params' );
				var sCurNavType = oNavParams.type;
				
				if ( sNavType == sCurNavType ) {
					
					var iCurPostTypeId = oNavParams.post_type_id;
					var sPostType = oPostTypes[ iCurPostTypeId ].slug;
					
					var sFullItemTitle = '';
					var sItemTitle = '';
					
					if ( 'category' == sPostType ) {
						
						var iCurCatId = oNavParams.cat_id;
						var oCurCat = oCatsNorm[ iCurCatId ];
						
						if ( oCurCat ) {
							sItemTitle = oNavParams.label.htmlEntities() || oCurCat.title.htmlEntities() ;
							sFullItemTitle = '%s (Category: %s)'.printf( sItemTitle, oCatTypes[ oCurCat.type ] );
						}
						
					} else if ( 'author' == sPostType ) {
						
						var iCurAuthorId = oNavParams.author_id;
						var oCurAuthor = oAuthorParams[ iCurAuthorId ];
						
						if ( oCurAuthor ) {
							sItemTitle = oNavParams.label.htmlEntities() || oCurAuthor.title.htmlEntities() ;
							sFullItemTitle = '%s (Author)'.printf( sItemTitle );
						}
						
					}
					
					eLi.find( 'span.item_title a' ).html( sFullItemTitle );
										
				}
				
			} );
			
		},
		
		init: function() {
			
			var eNavDlg = this.__elems.navDlg;
			var sTypePfx = this.pfx_type;
						
			//
			if ( this.disable_params ) {
				
				var sPostTypeDrpdwnSel = '#%spost_type_id'.printf( sTypePfx );
				var sPostTypeLabelSel = 'label[for=%spost_type_id]' .printf( sTypePfx );
				
				var sCatDrpdwnSel = '#%scat_id'.printf( sTypePfx );
				var sCatLabelSel = 'label[for=%scat_id]'.printf( sTypePfx );
				
				var sAuthorDrpdwnSel = '#%sauthor_id'.printf( sTypePfx );
				var sAuthorLabelSel = 'label[for=%sauthor_id]'.printf( sTypePfx );
				
				
				eNavDlg.find( sPostTypeDrpdwnSel ).attr( 'disabled', 'disabled' ).css( 'color', 'gray' );
				eNavDlg.find( sPostTypeLabelSel ).css( 'color', 'gray' );
				
				eNavDlg.find( sCatDrpdwnSel ).attr( 'disabled', 'disabled' ).css( 'color', 'gray' );
				eNavDlg.find( sCatLabelSel ).css( 'color', 'gray' );
				
				eNavDlg.find( sAuthorDrpdwnSel ).attr( 'disabled', 'disabled' ).css( 'color', 'gray' );
				eNavDlg.find( sAuthorLabelSel ).css( 'color', 'gray' );
				
			}
			
		},
		
		setup: function() {
			
			var _this = this;
			
			var oCatParams = this.cat_params;
			var oCatTypes = oCatParams.cat_types;
			var oCatsNorm = oCatParams.cats_norm;
			
			
			var eNavDlg = this.__elems.navDlg;
			var sNavType = this.type;
			var sTypePfx = this.pfx_type;
			
			var sPostTypeDrpdwnSel = '#%spost_type_id'.printf( sTypePfx );
			var sCatDrpdwnSel = '#%scat_id'.printf( sTypePfx );
			var sAuthorDrpdwnSel = '#%sauthor_id'.printf( sTypePfx );
			
			var sCatLabelSel = '.opt-%s label[for=%scat_id]'.printf( sNavType, sTypePfx );
			var sAuthorLabelSel = '.opt-%s label[for=%sauthor_id]'.printf( sNavType, sTypePfx );
			
			
			var ePostTypeDrpdwn = eNavDlg.find( sPostTypeDrpdwnSel );
			var eCatDrpdwn = eNavDlg.find( sCatDrpdwnSel );
			var eAuthorDrpdwn = eNavDlg.find( sAuthorDrpdwnSel );
			

			
			//// populate fields
			
			//
			$.each( this.post_types, function( i, val ) {
				ePostTypeDrpdwn.append(
					'<option value="%d">%s</option>'.printf( i, val.title )
				);
			} );
			
			//
			$.each( oCatsNorm, function( i, val ) {
				eCatDrpdwn.append(
					'<option value="%d">%s (%d)</option>'.printf( i, val.title, i )
				);
			} );
			
			//
			$.each( this.author_params, function( i, val ) {
				eAuthorDrpdwn.append(
					'<option value="%d">%s (%d)</option>'.printf( i, val.title, i )
				);
			} );
			
			
			//
			ePostTypeDrpdwn.on( 'change', function() {
				
				var eDrpdwn = $( this );
				
				var oNavParams = eNavDlg.data( 'selected_li' ).data( 'nav_params' );
				
				oNavParams[ 'post_type_id' ] = eDrpdwn.val();
				
				// trigger open event on the dialog
				eNavDlg.trigger( 'open' );
				
			} );
			
			
			
			//// add dialog triggers
			
			//
			eNavDlg.on( 'open', function( evt ) {
				
				var eDlg = $( this );
				
				var oNavParams = eDlg.data( 'selected_li' ).data( 'nav_params' );
				var sCurNavType = oNavParams.type;
				
				if ( sNavType == sCurNavType ) {
					
					eDlg.find( '.opt-common' ).hide();
					
					// show the appropriate options
					var iCurPostTypeId = oNavParams.post_type_id;
					
					var oPostTypes = _this.post_types;
					var sPostType = oPostTypes[ iCurPostTypeId ].slug;
					
					var sCatDisplayCss = ( 'category' == sPostType ) ? '' : 'none' ;
					var sAuthorDisplayCss = ( 'author' == sPostType ) ? '' : 'none' ;
					
					eDlg.find( sCatLabelSel ).css( 'display', sCatDisplayCss );
					eDlg.find( sCatDrpdwnSel ).css( 'display', sCatDisplayCss );
					
					eDlg.find( sAuthorLabelSel ).css( 'display', sAuthorDisplayCss );
					eDlg.find( sAuthorDrpdwnSel ).css( 'display', sAuthorDisplayCss );
					
				}
				
			} );
			
			//
			eNavDlg.on( 'reset', function( evt ) {
				
				var eDlg = $( this );
				
				var oNavParams = eDlg.data( 'selected_li' ).data( 'nav_params' );
				
				ePostTypeDrpdwn.selValue( oNavParams.post_type_id );
				eCatDrpdwn.selValue( oNavParams.cat_id );
				eAuthorDrpdwn.selValue( oNavParams.author_id );
				
				eDlg.find( '.opt-common' ).show();
				
			} );
						
			//
			eNavDlg.on( 'type_change', function( evt ) {
				
				var eDlg = $( this );
				
				var oNavParams = eDlg.data( 'selected_li' ).data( 'nav_params' );
				var sCurNavType = oNavParams.type;
				
				if ( sNavType == sCurNavType ) {
					eDlg.find( '.opt-common' ).hide();
				} else {
					eDlg.find( '.opt-common' ).show();				
				}
				
			} );
			
		}
		
	} );
	
} )( jQuery );
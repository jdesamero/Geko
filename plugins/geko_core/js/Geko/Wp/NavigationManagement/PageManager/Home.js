/*
 * "geko_core/js/Geko/Wp/NavigationManagement/PageManager/Home.js"
 * https://github.com/jdesamero/Geko
 *
 * Copyright (c) 2013 Joel Desamero.
 * Licensed under the MIT license.
 */

;( function ( $ ) {
		
	$.gekoNavigationPageManager.registerPlugin( {
		
		name: 'Geko_Wp_NavigationManagement_PageManager_Home',
		
		depends: 'Geko_Navigation_PageManager_ImplicitLabelAbstract',
		
		setup_li: function( eNavLi ) {
			
			//
			var _this = this;
			
			var sNavType = this.type;
			
			
			//
			eNavLi.on( 'update', function( evt ) {
				
				var eLi = $( this );
				
				var oNavParams = eLi.data( 'nav_params' );
				var sCurNavType = oNavParams.type;
				
				if ( sNavType == sCurNavType ) {
					
					var sHomeTitle = oNavParams.label.htmlEntities() || _this.homepage_title.htmlEntities();
					
					eLi.find( 'span.item_title a').html( sHomeTitle );
					
					eLi.find( 'a.link' ).attr( 'href', _this.homepage_url );
				}
				
			} );
			
		}
		
	} );
	
} )( jQuery );
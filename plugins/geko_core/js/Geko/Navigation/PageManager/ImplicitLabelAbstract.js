;( function ( $ ) {
	
	$.gekoNavigationPageManager.registerPlugin( {
		
		name: 'Geko_Navigation_PageManager_ImplicitLabelAbstract',
		
		setup: function() {
			
			var mgmt = this;
			var navDlg = this.__elems.navDlg;
			var sPrefix = this.__opts.prefix;
			
			//
			navDlg.bind( 'open', function( evt ) {
				
				var nav_params = $( this ).data( 'selected_li' ).data( 'nav_params' );
				
				if ( mgmt.type == nav_params.type ) {
					
					$( '.opt-common label[for=' + sPrefix + 'label]' ).html( 'Label Override' );
					$( '.opt-common label[for=' + sPrefix + 'title]' ).html( 'Title Override' );
				}
				
			} );
			
		}
		
	} );
	
	
} )( jQuery );
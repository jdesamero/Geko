( function( $ ) {
	
	//// helpers
	
	var getMenuId = function( eHoverTab, sPfx ) {
		
		var eLi = eHoverTab.closest( 'li' );
		
		if ( !eLi.data( 'hover-menu-id' ) ) {

			var aClasses = eLi.attr( 'class' ).split( ' ' );
			$.each( aClasses, function( i, v ) {
				
				if ( 0 === v.indexOf( sPfx ) ) {
					eLi.data( 'hover-menu-id', v.substring( sPfx.length ) );
					return false;		// found match, break
				}
				
			} );
			
		}
		
		return eLi.data( 'hover-menu-id' );
	}
	
	var isNotSticky = function( eRelTgt, aStickyIds ) {
		
		var bSticky = true;
		
		$.each( aStickyIds, function( i, v ) {
			if (
				( v != eRelTgt.attr( 'id' ) ) && 
				( 0 == eRelTgt.closest( '#%s'.printf( v ) ).length )
			) {
				bSticky = false;
				return false;
			}
		} );
		
		return bSticky;
	}
	
	//
	$.fn.gekoHovermenu = function( options ) {
		
		var opts = $.extend( {
			
			tab_prefix: 'tab-',
			menu_prefix: 'hover-',
			
			nav_sel: '#mainnav',
			tab_sel: 'li.hovermenu-tab > a',
			menu_sel: '.hover-menu',
			
			sticky_ids: []
			
		}, options );
		
		
		//
		return this.each( function() {

			var eHoverTab = $( this );
			var eHoverTabs = eHoverTab.closest( opts.nav_sel ).find( opts.tab_sel );
			
			var eHoverMenu = $( opts.menu_sel );
			
			
			
			//// tabs
			
			eHoverTab.hover(
				function() {
					
					// reset
					eHoverMenu.hide();
					eHoverTabs.removeClass( 'hover' );
					
					var eHvt = $( this );
					var sMenuSel = '#%s%s'.printf( opts.menu_prefix, getMenuId( eHvt, opts.tab_prefix ) );
					
					$( sMenuSel ).slideDown( 'slow' );
					eHvt.addClass( 'hover' );
					
				},
				function( e ) {

					var eHvt = $( this );
					var eRelTgt = $( e.relatedTarget );
					
					var sMenuId = '%s%s'.printf( opts.menu_prefix, getMenuId( eHvt, opts.tab_prefix ) );
					var sMenuSel = '#%s'.printf( sMenuId );
					
					if (
						( sMenuId != eRelTgt.attr( 'id' ) ) && 
						( 0 == eRelTgt.closest( sMenuSel ).length ) &&
						isNotSticky( eRelTgt, opts.sticky_ids )
					) {
						$( sMenuSel ).hide();
						eHoverTabs.removeClass( 'hover' );
					}
					
				}
			);
			
			
			//// hover menu
			
			eHoverMenu.hover(
				function() { },
				function() {
					$( this ).hide();			
				}					
			);
			
			
			//// position

			var positionHoverMenu = function() {
				
				var winWdt = $( window ).width();
				var iPosOffset = parseInt( ( ( winWdt / 2 ) - ( eHoverMenu.width() / 2 ) ) - 0 );
				
				eHoverMenu.css( 'left', '%dpx'.printf( iPosOffset ) );
			}
			
			
			//// init
			
			// $( window ).resize( positionHoverMenu );
			positionHoverMenu();
			
			
			
		} );
		
	};	

} )( jQuery );

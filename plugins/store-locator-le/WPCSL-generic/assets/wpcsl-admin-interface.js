/**
 * wpCSL admin UI JS
 */

(function ($) {

  wpcslAdminInterface = {
  
/**
 * toggle_nav_tabs()
 *
 */
 
 	toggle_nav_tabs: function () {
 		var flip = 0;
	
		$( '#expand_options' ).click( function(){
			if( flip == 0 ){
				flip = 1;
				$( '#wpcsl_container #wpcsl-nav' ).hide();
				$( '#wpcsl_container #content' ).width( 785 );
				$( '#wpcsl_container .group' ).add( '#wpcsl_container .group h1' ).show();

				$(this).text( '[-]' );

			} else {
				flip = 0;
				$( '#wpcsl_container #wpcsl-nav' ).show();
				$( '#wpcsl_container #content' ).width( 595 );
				$( '#wpcsl_container .group' ).add( '#wpcsl_container .group h1' ).hide();
				$( '#wpcsl_container .group:first' ).show();
				$( '#wpcsl_container #wpcsl-nav li' ).removeClass( 'current' );
				$( '#wpcsl_container #wpcsl-nav li:first' ).addClass( 'current' );

				$(this).text( '[+]' );

			}

		});
 	}, // End toggle_nav_tabs()

/**
 * load_first_tab()
 */
 
 	load_first_tab: function () {
        $( '.group' ).hide();
        var selectedNav = $('#selected_nav_element').val();
        if (selectedNav == '') {
            $( '.group:has(".section"):first' ).fadeIn(); // Fade in the first tab containing options (not just the first tab).
        } else {
            $(selectedNav).fadeIn();
        }
 	}, // End load_first_tab()
 	
/**
 * open_first_menu()
 */
 
 	open_first_menu: function () {
        $('#wpcsl-nav li.current.has-children:first ul.sub-menu').slideDown().addClass( 'open' ).children( 'li:first' ).addClass( 'active' ).parents( 'li.has-children' ).addClass( 'open' );
 	}, // End open_first_menu()
 	
/**
 * toggle_nav_menus()
 */
 
 	toggle_nav_menus: function () {
 		$( '#wpcsl-nav li.has-children > a' ).click( function ( e ) {
 			if ( $( this ).parent().hasClass( 'open' ) ) { return false; }
 			
 			$( '#wpcsl-nav li.top-level' ).removeClass( 'open' ).removeClass( 'current' );
 			$( '#wpcsl-nav li.active' ).removeClass( 'active' );
 			if ( $( this ).parents( '.top-level' ).hasClass( 'open' ) ) {} else {
 				$( '#wpcsl-nav .sub-menu.open' ).removeClass( 'open' ).slideUp().parent().removeClass( 'current' );
 				$( this ).parent().addClass( 'open' ).addClass( 'current' ).find( '.sub-menu' ).slideDown().addClass( 'open' ).children( 'li:first' ).addClass( 'active' );
 			}
 			
 			// Find the first child with sections and display it.
 			var clickedGroup = $( this ).parent().find( '.sub-menu li a:first' ).attr( 'href' );
 			
 			if ( clickedGroup != '' ) {
 				$( '.group' ).hide();
 				$( clickedGroup ).fadeIn();
 			}
 			return false;
 		});
 	}, // End toggle_nav_menus()
 	
/**
 * toggle_collapsed_fields()
 */
 
 	toggle_collapsed_fields: function () {
		$( '.group .collapsed' ).each(function(){
			$( this ).find( 'input:checked' ).parent().parent().parent().nextAll().each( function(){
				if ($( this ).hasClass( 'last' ) ) {
					$( this ).removeClass( 'hidden' );
					return false;
				}
				$( this ).filter( '.hidden' ).removeClass( 'hidden' );
				
				$( '.group .collapsed input:checkbox').click(function ( e ) {
					wpcslAdminInterface.unhide_hidden( $( this ).attr( 'id' ) );
				});

			});
		});
 	}, // End toggle_collapsed_fields()

/**
 * setup_nav_highlights()
 */
 
 	setup_nav_highlights: function () {
	 	// Highlight the first item by default.
        var selectedNav = $('#selected_nav_element').val();
        if (selectedNav == '') {
            $( '#wpcsl-nav li.top-level:first' ).addClass( 'current' ).addClass( 'open' );
        } else {
            $( '#wpcsl-nav li.top-level:has(a[href="'+selectedNav+'"])').addClass( 'current' ).addClass( 'open' );
        }
		
		// Default single-level logic.
		$( '#wpcsl-nav li.top-level' ).not( '.has-children' ).find( 'a' ).click( function ( e ) {
			var thisObj = $( this );
			var clickedGroup = thisObj.attr( 'href' );
			
			if ( clickedGroup != '' ) {
                $( '#selected_nav_element').val(clickedGroup);
				$( '#wpcsl-nav .open' ).removeClass( 'open' );
				$( '.sub-menu' ).slideUp();
				$( '#wpcsl-nav .active' ).removeClass( 'active' );
				$( '#wpcsl-nav li.current' ).removeClass( 'current' );
				thisObj.parent().addClass( 'current' );
				
				$( '.group' ).hide();
				$( clickedGroup ).fadeIn();
				
				return false;
			}
		});
		
		$( '#wpcsl-nav li:not(".has-children") > a:first' ).click( function( evt ) {
			var parentObj = $( this ).parent( 'li' );
			var thisObj = $( this );
			
			var clickedGroup = thisObj.attr( 'href' );
			
			if ( $( this ).parents( '.top-level' ).hasClass( 'open' ) ) {} else {
				$( '#wpcsl-nav li.top-level' ).removeClass( 'current' ).removeClass( 'open' );
				$( '#wpcsl-nav .sub-menu' ).removeClass( 'open' ).slideUp();
				$( this ).parents( 'li.top-level' ).addClass( 'current' );
			}
		
			$( '.group' ).hide();
			$( clickedGroup ).fadeIn();
		
			evt.preventDefault();
			return false;
		});
		
		// Sub-menu link click logic.
		$( '.sub-menu a' ).click( function ( e ) {
			var thisObj = $( this );
			var parentMenu = $( this ).parents( 'li.top-level' );
			var clickedGroup = thisObj.attr( 'href' );
			
			if ( $( '.sub-menu li a[href="' + clickedGroup + '"]' ).hasClass( 'active' ) ) {
				return false;
			}
			
			if ( clickedGroup != '' ) {
				parentMenu.addClass( 'open' );
				$( '.sub-menu li, .flyout-menu li' ).removeClass( 'active' );
				$( this ).parent().addClass( 'active' );
				$( '.group' ).hide();
				$( clickedGroup ).fadeIn();
			}
			
			return false;
		});
 	}, // End setup_nav_highlights()

/**
 * init_flyout_menus()
 */
 
 	init_flyout_menus: function () {
 		// Only trigger flyouts on menus with closed sub-menus.
 		$( '#wpcsl-nav li.has-children' ).each ( function ( i ) {
 			$( this ).hover(
	 			function () {
	 				if ( $( this ).find( '.flyout-menu' ).length == 0 ) {
		 				var flyoutContents = $( this ).find( '.sub-menu' ).html();
		 				var flyoutMenu = $( '<div />' ).addClass( 'flyout-menu' ).html( '<ul>' + flyoutContents + '</ul>' );
		 				$( this ).append( flyoutMenu );
	 				}
	 			}, 
	 			function () {
	 			}
	 		);
 		});
 		
 		// Add custom link click logic to the flyout menus, due to custom logic being required.
 		$( '.flyout-menu a' ).live( 'click', function ( e ) {
 			var thisObj = $( this );
 			var parentObj = $( this ).parent();
 			var parentMenu = $( this ).parents( '.top-level' );
 			var clickedGroup = $( this ).attr( 'href' );
 			
 			if ( clickedGroup != '' ) {
	 			$( '.group' ).hide();
	 			$( clickedGroup ).fadeIn();
	 			
	 			// Adjust the main navigation menu.
	 			$( '#wpcsl-nav li' ).removeClass( 'open' ).removeClass( 'current' ).find( '.sub-menu' ).slideUp().removeClass( 'open' );
	 			parentMenu.addClass( 'open' ).addClass( 'current' ).find( '.sub-menu' ).slideDown().addClass( 'open' );
	 			$( '#wpcsl-nav li.active' ).removeClass( 'active' );
	 			$( '#wpcsl-nav a[href="' + clickedGroup + '"]' ).parent().addClass( 'active' );
 			}
 			
 			return false;
 		});
 	}, // End init_flyout_menus()

/**
 * unhide_hidden()
 */
 
 	unhide_hidden: function ( obj ) {
 		obj = $( '#' + obj ); // Get the jQuery object.
 		
		if ( obj.attr( 'checked' ) ) {
			obj.parent().parent().parent().nextAll().slideDown().removeClass( 'hidden' ).addClass( 'visible' );
		} else {
			obj.parent().parent().parent().nextAll().each( function(){
				if ( $( this ).filter( '.last' ).length ) {
					$( this ).slideUp().addClass( 'hidden' );
				return false;
				}
				$( this ).slideUp().addClass( 'hidden' );
			});
		}
 	} // End unhide_hidden()
  
  }; // End wpcslAdminInterface Object // Don't remove this, or the sky will fall on your head.

/**
 * Execute the above methods in the wpcslAdminInterface object.
 */
	$(document).ready(function () {	
		wpcslAdminInterface.toggle_nav_tabs();
		wpcslAdminInterface.load_first_tab();
		wpcslAdminInterface.toggle_collapsed_fields();
		wpcslAdminInterface.setup_nav_highlights();
		wpcslAdminInterface.toggle_nav_menus();
		wpcslAdminInterface.init_flyout_menus();
		wpcslAdminInterface.open_first_menu();
	
	});
  
})(jQuery);


// Expand help icons
// 
jQuery(document).ready(function($) {
    $('.<?php echo $this->css_prefix;?>-moreicon').click(function(){
        $(this).siblings('.<?php echo $this->css_prefix; ?>-moretext').toggle();
    });
});


// Some legacy stuff - like expand/collapse divs (defunct?)
// TODO: check if this is needed
//
jQuery(document).ready(function($) {
    $('.postbox').children('h3, .handlediv').click(function(){
        $(this).siblings('.inside').toggle();
    });
});

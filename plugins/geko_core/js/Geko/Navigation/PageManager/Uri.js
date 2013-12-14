;( function ( $ ) {
	
	$.gekoNavigationPageManager.registerPlugin( {
		
		name: 'Geko_Navigation_PageManager_Uri',
		
		setup_li: function( li ) {
			
			//
			var mgmt = this;
			var navDlg = this.__elems.navDlg;
			
			var uri_sel = '#' + this.pfx_type + 'uri';
			var strict_match_sel = '#' + this.pfx_type + 'strict_match';
			var ignore_vars_sel = '#' + this.pfx_type + 'ignore_vars';
			
			//
			li.bind( 'pre_update', function( evt ) {
				
				var nav_params = $( this ).data( 'nav_params' );
				
				if ( mgmt.type == nav_params.type ) {
					nav_params.uri = navDlg.find( uri_sel ).val();
					nav_params.strict_match = navDlg.find( strict_match_sel ).attr('checked');
					nav_params.ignore_vars = navDlg.find( ignore_vars_sel ).val();
				}
				
			} );
			
			//
			li.bind( 'update', function( evt ) {
				
				var nav_params = $( this ).data( 'nav_params' );
				
				if ( mgmt.type == nav_params.type ) {
					if ( $( this ).hasClass( mgmt.__opts.template ) && !nav_params.label ) {
						$( this ).find( 'span.item_title a' ).html( mgmt.default_label.htmlEntities() );
					}
					
					$( this ).find('a.link').attr( 'href', nav_params.uri );
				}
				
			} );
			
		},
		
		init: function() {
			
			var mgmt = this;
			var navDlg = this.__elems.navDlg;
			var strict_match = '#' + this.pfx_type + 'strict_match';
			
			//
			if ( this.disable_params ) {
				navDlg.find( strict_match ).attr( 'disabled', 'disabled' ).css( 'color', 'gray' );
				navDlg.find( 'label[for=' + this.pfx_type + 'strict_match]' ).css( 'color', 'gray' );
			}
			
		},
		
		setup: function() {
			
			//
			var mgmt = this;
			var navDlg = this.__elems.navDlg;
			var sPrefix = this.__opts.prefix;
			
			var label_sel = '#' + sPrefix + 'label';
			var uri_sel = '#' + this.pfx_type + 'uri';
			var strict_match_sel = '#' + this.pfx_type + 'strict_match';
			var ignore_vars_sel = '#' + this.pfx_type + 'ignore_vars';
			
			//
			$( '#' + this.pfx_type + 'strict_match' ).click( function() {
				
				var nav_params = navDlg.data( 'selected_li' ).data( 'nav_params' );
				
				nav_params.strict_match = $( this ).attr( 'checked' );
				
				// trigger open event on the dialog
				navDlg.trigger( 'open' );
				
			} );
			
			//
			navDlg.bind( 'open', function( evt ) {
				
				var sLabel = $( this ).find( label_sel ).val();
				
				var selectedLi = $( this ).data( 'selected_li' );
				var nav_params = selectedLi.data( 'nav_params' );
				var nav_params_prev = selectedLi.data( 'nav_params_prev' );
				
				if ( mgmt.type == nav_params.type ) {
					
					if ( mgmt.type != nav_params_prev.type && sLabel ) nav_params.label = sLabel;
					if ( !sLabel && !nav_params.label ) $( this ).find( label_sel ).val( mgmt.default_label );
				
					// only show ignore_vars if strict_match is checked
					$( this ).find( '.opt-' + mgmt.type + ' label[for=' + mgmt.pfx_type + 'ignore_vars]' ).css( 'display', ( ( nav_params.strict_match ) ? '' : 'none' ) );
					$( this ).find( ignore_vars_sel ).css( 'display', ( ( nav_params.strict_match ) ? '' : 'none' ) );
					
				} else {
					if ( mgmt.default_label == sLabel && !nav_params.label ) $( this ).find( label_sel ).val( '' );
				}
								
			} );
			
			//
			navDlg.bind( 'reset', function( evt ) {
				
				var nav_params = $( this ).data( 'selected_li' ).data( 'nav_params' );
				
				$( this ).find( uri_sel ).val( ( nav_params.uri ) ? nav_params.uri : '' );
				$( this ).find( strict_match_sel ).attr( 'checked', ( nav_params.strict_match ) ? true : false );
				$( this ).find( ignore_vars_sel ).val( ( nav_params.ignore_vars ) ? nav_params.ignore_vars : '' );
				
			} );
						
		}
		
	} );
	
} )( jQuery );
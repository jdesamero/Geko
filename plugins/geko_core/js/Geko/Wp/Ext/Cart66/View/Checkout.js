/**
 * This script requires the following variables from the parent page:
 *   $jqErrors -- an array of jQuery error information
 *   $s -- an array of shipping information
 *   $b -- an array of billing information
 *   $p -- an array of payment information
 */
( function( $ ) {
	
	//
	$.fn.listenForChange = function( options ) {
		
		settings = $.extend( {
			interval: 200 // in microseconds
		}, options );
		
		var jquery_object = this;
		var current_focus = null;
		
		jquery_object.focus( function() {
			current_focus = this;
		} ).blur( function() {
			current_focus = null;
		} );
		
		setInterval( function() {
			
			// allow
			jquery_object.each( function() {
				
				// set data cache on element to input value if not yet set
				if ( $( this ).data( 'change_listener' ) == undefined ) {
					$( this ).data( 'change_listener', $( this ).val() );
					return;
				}
			
				// return if the value matches the cache
				if ( $( this ).data( 'change_listener' ) == $( this ).val() ) {
					return;
				}
			
				// ignore if element is in focus (since change event will fire on blur)
				if ( this == current_focus ) {
					return;
				}
			
				// if we make it here, manually fire the change event and set the new value
				$( this ).trigger( 'change' );
				$( this ).data( 'change_listener', $( this ).val() );
				
			} );
		
		}, settings.interval );
		
		return this;		
	};
	
	
	//
	function setState( frm, kind ) {
		
		frm.find( 'select[name="' + kind + '[state]"]' ).empty();
		
		var st = frm.find( 'select[name="' + kind + '[country]"]' ).val();
		
		if ( typeof C66.zones[ st ] == 'undefined' ) {
			
			frm.find( 'select[name="' + kind + '[state]"]' ).attr( 'disabled', 'disabled' );
			frm.find( 'select[name="' + kind + '[state]"]' ).empty();
			frm.find( 'select[name="' + kind + '[state]"]' ).hide(); 
			frm.find( 'input[name="' + kind + '[state_text]"]' ).show();
		
		} else {
			
			frm.find( 'select[name="' + kind + '[state]"]' ).removeAttr( 'disabled' );
			frm.find( 'select[name="' + kind + '[state]"]' ).empty(); 
			frm.find( 'select[name="' + kind + '[state]"]' ).show(); 
			frm.find( 'input[name="' + kind + '[state_text]"]' ).hide();
			
			for ( var code in C66.zones[ st ] ) {
				frm.find( 'select[name="' + kind + '[state]"]' ).append(
					'<option value="' + code + '">' + C66.zones[ st ][ code ] + '<\/option>'
				);
			}
		}
		
		//
		switch ( st ) {
			case 'US':
				$( '.' + kind + '-state_label' ).html( C66.text_state + ': ' );
				$( '.' + kind + '-zip_label' ).html( C66.text_zip_code + ': ' );
				break;
			case 'AU':
				$( '.' + kind + '-state_label' ).html( C66.text_state + ': ' );
				$( '.' + kind + '-zip_label' ).html( C66.text_post_code + ': ' );
				break;
			default:
				$( '.' + kind + '-state_label' ).html( C66.text_province + ': ' );
				$( '.' + kind + '-zip_label' ).html( C66.text_post_code + ': ' );
		}
	}
	
	//
	function initStateField( frm, kind, country ) {
		
		if ( typeof C66.zones[ country ] == 'undefined' ) {
			frm.find( 'select[name="' + kind + '[state]"]' ).attr( 'disabled', 'disabled' );
			frm.find( 'select[name="' + kind + '[state]"]' ).empty();
			frm.find( 'select[name="' + kind + '[state]"]' ).hide();
			frm.find( 'input[name="' + kind + '[state_text]"]' ).show();
		}
		
		setState( frm, kind );
	}
	
	
	//
	test = '';
	
	//
	function updateAjaxTax( target ) {
		
		var sabCbx = $( '.sameAsBilling' );
		var taxed = $( '.ajax-tax-cart' ).val();
    	
    	//// do checks
    	
    	// shipping address is different, changing billing info should have no effect
    	if (
    		( !sabCbx.is( ':checked' ) ) && 
    		(
    			( 'billing-state' == target.attr( 'id' ) ) || 
    			( 'billing-state_text' == target.attr( 'id' ) ) || 
    			( 'billing-zip' == target.attr( 'id' ) )
    		)
    	) {
    		return false;
    	}
    	
    	
		// alert( taxed );
		
		if ( 'true' === taxed ) {
      		
			var ajaxurl = $( '#confirm-url' ).val();
			var state = $( '#billing-state' ).val();
			
			if ( state == null ) {
				state = '';
			}
			
			var zip = $( '#billing-zip' ).val();
			var state_text = $('#billing-state_text').val();
			
			if ( ( sabCbx.length != 0 ) && !( sabCbx.is( ':checked' ) ) ) {
        		
				if ( $( '#shipping-zip' ).length != 0 ) {
					var zip = $( '#shipping-zip' ).val();
				}
				
				if ( $( '#shipping-state_text' ).length != 0 ) {
					var state_text = $( '#shipping-state_text' ).val();
				}
        		
				if ( $( '#shipping-state' ).length != 0 ) {
					var state = $( '#shipping-state' ).val();
					if ( state == null ) {
						state = '';
					}
				}
      			
			} else if ( $( '#billing-zip' ).length == 0 ) {
				
				if ( $( '#shipping-zip' ).length != 0 ) {
					var zip = $( '#shipping-zip' ).val();
				}
				
				if ( $( '#shipping-state_text' ).length != 0 ) {
					var state_text = $( '#shipping-state_text' ).val();
				}
				
				if ( $( '#shipping-state' ).length != 0 ) {
					var state = $( '#shipping-state' ).val();
					if ( state == null ) {
						state = '';
					}
				}
        	}
      		
			/* /
			if ( zip == '' ) {
				return false;
			}
			/* */
			
			if ( !state && !state_text ) {
				return false;
			}
			
			$( '.ajax-spin' ).show();
			
			var gateway = $( '#cart66-gateway-name' ).val();
      
			$.ajax( {
				type: 'POST',
				url: ajaxurl + '=4',
				data: {
					state: state,
					state_text: state_text,
					zip: zip,
					gateway: gateway
				},
				dataType: 'json',
				success: function ( response ) {
					
					if ( response.tax != '$0.00' ) {
						$( '.tax-row' ).removeClass( 'hide-tax-row' ).addClass( 'show-tax-row' );
						$( '.tax-block' ).removeClass( 'hide-tax-block' ).addClass( 'show-tax-block' );
					}
					
					$( '.tax-amount' ).html( response.tax );
					$( '.grand-total-amount' ).html( response.total );
					$( '.tax-rate' ).html( response.rate );
					$( '.ajax-spin' ).hide();
          			
					if ( test == '' ) {
						test = 'running';
						$( '.tax-update' ).fadeIn( 500 ).delay( 2300 ).fadeOut( 500 );
						$( '.tax-update' ).queue( function () {
							test = '';
							$( this ).dequeue();
						} );
					}
					
				},
				error: function( xhr,err ) {
					// alert( 'readyState: ' + xhr.readyState + "\nstatus: " + xhr.status );
				}
			} );
			
		}
		
		return false;
	}
	
	
	//
	$( document ).ready( function() {
    	
    	var sabCbx = $( '.sameAsBilling' );		// same as billing checkbox
    	
		var shipping_countries = $( '#shipping-country' ).html();
		var billing_countries = $( '#billing-country' ).html();
		
		// Dynamically configure billing state based on country
		$( '.billing_countries' ).change( function() { 
			setState( $( this ).closest( 'form' ), 'billing' );
		} );
		
		// Dynamically configure shipping state based on country
		$( 'select[name="shipping[country]"]' ).on( 'change', function() { 
			setState( $( this ).closest( 'form' ), 'shipping' );
		} );
		
		//
		if ( C66.same_as_billing == 1 ) {
			sabCbx.attr( 'checked', 'checked' );
		} else {
			sabCbx.removeAttr( 'checked' );
		}
		
		//
		$( '.shippingAddress' ).css( 'display', C66.shipping_address_display );
    	
    	
    	// listen for change
		$( '#billing-state_text, #billing-state, #billing-zip' ).addClass( 'ajax-tax' );
		$( '#shipping-state_text, #shipping-state, #shipping-zip' ).addClass( 'ajax-tax' );
		
    	
		//
		sabCbx.each( function() {
			
			var thisSabCbx = $( this );
			var frm = thisSabCbx.closest( 'form' );
			
			if ( thisSabCbx.is( ':checked' ) ) {
				
				frm.find( '.billing_countries' ).html( shipping_countries );
				setState( frm, 'billing' );
				
				$( '.limited-countries-label-billing' ).show();
				
				// $( '#billing-state_text, #billing-state, #billing-zip' ).addClass( 'ajax-tax' );
				// $( '#shipping-state_text, #shipping-state, #shipping-zip' ).removeClass( 'ajax-tax' );
				
				$( '#billing_tax_update' ).addClass( 'tax-update' ).show();
				$( '#shipping_tax_update' ).removeClass( 'tax-update' ).hide();
			
			} else {
			
				frm.find( '.billing_countries' ).html( billing_countries );
				setState( frm, 'billing' );
				
				$( '.limited-countries-label-billing' ).hide();
				
				// $( '#billing-state_text, #billing-state, #billing-zip' ).removeClass( 'ajax-tax' );
				// $( '#shipping-state_text, #shipping-state, #shipping-zip' ).addClass( 'ajax-tax' );
				
				$( '#billing_tax_update' ).removeClass( 'tax-update' ).hide();
				$( '#shipping_tax_update' ).addClass( 'tax-update' ).show();
			}
			
		} );
    	
		//
		sabCbx.click( function() {
			
			var thisSabCbx = $( this );
			var frm = thisSabCbx.closest( 'form' );
			
			if ( thisSabCbx.is( ':checked' ) ) {
				
				var billing_country = frm.find( '.billing_countries' ).val();
				
				frm.find( '.billing_countries' ).html( shipping_countries );
				frm.find( '.billing_countries' ).val( billing_country );
				
				frm.find( '.billing_countries option' ).each( function() {
					if (
						( $( this ).val() == frm.find( '.billing_countries' ).val() ) && 
						( $( this ).is( ':disabled' ) )
					) {
						frm.find( '.billing_countries' ).val( '' );
					}
				} );
				
				// setState( frm, 'billing' );
				$( '.limited-countries-label-billing' ).show();
				frm.find( '.shippingAddress' ).css( 'display', 'none' );
				
				// $( '#billing-state_text, #billing-state, #billing-zip' ).addClass( 'ajax-tax' );
				// $( '#shipping-state_text, #shipping-state, #shipping-zip' ).removeClass( 'ajax-tax' );
				
				$( '#billing_tax_update' ).addClass( 'tax-update' ).show();
				$( '#shipping_tax_update' ).removeClass( 'tax-update' ).hide();
			
			} else {
				
				frm.find( '.shippingAddress' ).css( 'display', 'block' );
				var billing_country = frm.find( '.billing_countries' ).val();
				frm.find( '.billing_countries' ).html( billing_countries );
				frm.find( '.billing_countries' ).val( billing_country );
				
				// setState( frm, 'billing' );
				$( '.limited-countries-label-billing' ).hide();
				
				// $( '#billing-state_text, #billing-state, #billing-zip' ).removeClass( 'ajax-tax' );
				// $( '#shipping-state_text, #shipping-state, #shipping-zip' ).addClass( 'ajax-tax' );
				
				$( '#billing_tax_update' ).removeClass( 'tax-update' ).hide();
				$( '#shipping_tax_update' ).addClass( 'tax-update' ).show();
				
			}
			
			updateAjaxTax( thisSabCbx );
		} );
		
		$( '#billing-state, #billing-zip, #billing-state_text, #shipping-state, #shipping-zip, #shipping-state_text' ).listenForChange();
		
		$( '.ajax-tax' ).on( 'change', function() {
			var target = $( this );
			updateAjaxTax( target );
		} );
		
		
		
		/* /
		$( '#billing-state_text, #shipping-state_text' ).val( 'ON' );
    	
    	// test
    	$( '.ajax-tax' ).each( function() {
    		var e = $( this );
    		console.log( e[ 0 ].nodeName + ' id: ' + e.attr( 'id' ) + ' class: ' + e.attr( 'class' ) + ' name: ' + e.attr( 'name' ) + ' val: ' + e.val() );
    	} );
    	/* */
		
    	/* /
		var billState = ( C66.billing_state ) ? C66.billing_state : 'ON' ;
    	var billCountry = ( C66.billing_country ) ? C66.billing_country : 'CA' ;
    	
    	var shipState = ( C66.shipping_state ) ? C66.shipping_state : 'ON' ;
    	var shipCountry = ( C66.shipping_country ) ? C66.shipping_country : 'CA' ;
    	/* */
		
		/* */
		var billState = C66.billing_state;
    	var billCountry = C66.billing_country;
    	
    	var shipState = C66.shipping_state;
    	var shipCountry = C66.shipping_country;
		/* */
		
		if ( billCountry ) {
			$( '.billing_countries' ).each( function( index ) {
				var frm = $( this ).closest( 'form' );
				initStateField( frm, 'billing', billCountry );
			} );
		}
		
		if ( shipCountry ) {
			$( '.shipping_countries' ).each( function( index ) {
				var frm = $( this ).closest( 'form' );
				initStateField( frm, 'shipping', shipCountry );
			} );
		}
    	
    	
		$( '#billing-state' ).val( billState );
		$( '#shipping-state' ).val( shipState );
		
		$( '#payment-cardType' ).val( C66.card_type );
		
		// prevent duplicate submissions
		$( C66.form_name ).submit( function() {
			$( '.Cart66CompleteOrderButton' ).attr( 'disabled', 'disabled' );
		} );
		
		$( C66.error_field_names ).each( function( key, field ) {
			$( field ).addClass( 'errorField' );
		} );
    	
    	
    	var frm2 = $( this ).closest( 'form' );
    	setState( frm2, 'shipping' );
    	
    	
	} );
	
	
	
	//
	$.gekoWpCart66ViewCheckout = function( oParams ) {
		
		var checkoutForm = $( 'form.checkout_form' );
		var checkoutButtonDiv = $( '#Cart66CheckoutButtonDiv' );
		
		var createAccDiv = $( '#checkoutCreateAccount' );
		
		var loginForm = $( '#checkoutLoginForm' );
		
		
		// toggle login form
		
		var showCheckout = function() {
			loginForm.hide();
			checkoutForm.show();
			checkoutButtonDiv.show();					
		};
		
		var hideCheckout = function() {
			checkoutForm.hide();
			checkoutButtonDiv.hide();
			loginForm.show();					
		};					
		
		
		loginForm.find( 'a.next_step' ).click( function() {
			showCheckout();
			return false;
		} );
		
		createAccDiv.find( 'a.log_in' ).click( function() {
			hideCheckout();
			return false;
		} );
		
		
		// login form functionality
		
		
		loginForm.gekoAjaxForm( {
			status: oParams.status,
			process_script: oParams.script.process,
			action: '&action=Gloc_Service_Profile&subaction=login',
			validate: function( form, errors ) {
				
				var email = form.getTrimVal( '#chklog-email' );
				var password = form.getTrimVal( '#chklog-pass' );
				
				if ( !email ) {
					errors.push( C66.text_enter_email );
					form.errorField( '#chklog-email' );
				} else {
					if ( !form.isEmail( email ) ) {
						errors.push( C66.text_enter_valid_email );
						form.errorField( '#chklog-email' );
					}
				}
				
				if ( !password ) {
					errors.push( C66.text_enter_password );
					form.errorField( '#chklog-pass' );
				} else {
					if ( password.length < 6 ) {
						errors.push( C66.text_enter_longer_password );
						form.errorField( '#chklog-pass' );
					}
				}
				
				return errors;
				
			},
			process: function( form, res, status ) {
				if ( status.login == parseInt( res.status ) ) {
					// reload page
					window.location = oParams.script.curpage;
				} else if ( status.not_activated == parseInt( res.status ) ) {
					form.error( C66.text_activate_account );
				} else {
					form.error( C66.text_login_failed );
				}
			}
		} );
		
		
		// toggle create account
		
		var liPass = createAccDiv.find( '#createacc-pass' ).closest( 'li' );
		var liConfPass = createAccDiv.find( '#createacc-confirm-pass' ).closest( 'li' );
		var liTerms = createAccDiv.find( '#createacc-terms-agree' ).closest( 'li' );
		
		var cbxDontCreate = createAccDiv.find( '#createacc-dont-create' );
		
		cbxDontCreate.click( function() {
			
			var cbx = $( this );
			
			if ( cbx.is( ':checked' ) ) {
				liPass.hide();
				liConfPass.hide();
				liTerms.hide();
			} else {
				liPass.show();
				liConfPass.show();
				liTerms.show();
			}
			
		} );
		
		
		// init
		
		if ( oParams.has_errors ) {
			
			var errorHash = oParams.error_hash;
			
			// showCheckout();
			
			$.each( errorHash, function( k, v ) {
				
				var match = oParams.errors[ k ];
				if ( match ) {
					checkoutForm.find( '#' + v ).addClass( 'errorField' );
				}
				
			} );
		};
		
		if ( oParams.dont_create_account ) {
			cbxDontCreate.attr( 'checked', 'checked' );
			liPass.hide();
			liConfPass.hide();
			liTerms.hide();
		}
		
	};
	
	
} )( jQuery );


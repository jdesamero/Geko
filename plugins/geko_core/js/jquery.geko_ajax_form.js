;( function ( $ ) {
	
	$.fn.gekoAjaxForm = function( options ) {

		var opts = $.extend( {
			status: {},
			action: '',
			process_script: '',
			classes: {
				init: '.error, .success',
				loading: '.loading',
				form_elems: 'input, select, textarea',
				error: '.error',
				success: '.success',
				error_field: 'error_field'
			},
			form_top: null,
			use_jquery_form: false,
			data_type: 'json',
			validate: function( form, errors ) {
				return errors;
			},
			process: function( form, res ) { },
			validate_success: function( form ) { },
			validate_error: null
		}, options );
		
		var formatMsg = function( msg ) {
			if ( 'array' == $.type( msg ) ) {
				var fmt = '';
				$.each( msg, function( i, v ) {
					fmt += v + '<br \/>';
				} );
				return fmt;
			}
			return msg;
		}
		
		//
		return this.each( function() {
			
			var form = $( this );
			
			
			
			//// attach helper functions to forms
			
			//
			form.success = function( msg ) {
				form.find( opts.classes.success ).html( formatMsg( msg ) ).show();
				form.find( opts.classes.loading ).hide();
				return form;
			}
			
			//
			form.successLoading = function( msg ) {
				form.find( opts.classes.success ).html( formatMsg( msg ) ).show();
				return form;
			}
			
			//
			form.error = function( msg ) {
				form.find( opts.classes.error ).html( formatMsg( msg ) ).show();
				form.find( opts.classes.loading ).hide();
				return form;
			}
			
			//
			form.errorLoading = function( msg ) {
				form.find( opts.classes.error ).html( formatMsg( msg ) ).show();
				return form;
			}
			
			//
			form.errorField = function( elemSel ) {
				form.find( elemSel ).addClass( opts.classes.error_field );
				return form;
			}
			
			//
			form.getTrimVal = function( elemSel ) {
				return $.trim( form.find( elemSel ).val() )
			}
			
			
			
			//// attach helper validator methods
			
			form.isEmail = function( val ) {
				var emailTest = /^[a-z0-9\._-]+@([a-z0-9_-]+\.)+[a-z]{2,6}$/i;
				return ( emailTest.test( val ) ) ? true : false ;
			}
			
			form.isUrl = function( val ) {
				var urlTest = /(ftp|http|https):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?/i;
				return ( urlTest.test( val ) ) ? true : false ;
			}
			
			// canadian postal code
			form.isPostalCode = function( val ) {
				var postalTest = /^[a-zA-Z][0-9][a-zA-Z][ ]*[0-9][a-zA-Z][0-9]$/;
				return ( postalTest.test( val ) ) ? true : false ;
			}
			
			
			//// bind events
			
			form.bind( 'validate', function( evt, custom_validate, custom_validate_success, custom_validate_error ) {
				
				var errors = [];
				
				if ( custom_validate ) {
					errors = custom_validate( form, errors );
				} else {
					errors = opts.validate( form, errors );
				}
				
				if ( errors.length ) {
					
					if ( custom_validate_error ) {
						custom_validate_error( form );					
					} else if ( opts.validate_error ) {
						opts.validate_error( form );					
					} else {
						form.find( opts.classes.error ).html( formatMsg( errors ) ).show();
						form.find( opts.classes.loading ).hide();
						if ( opts.form_top ) window.location = opts.form_top;
					}
					
				} else {
					
					if ( custom_validate_success ) {
						custom_validate_success( form );
					} else {
						opts.validate_success( form );
					}
				}
				
			} );
			
			form.bind( 'reset_errors', function( evt ) {
				form.find( opts.classes.error ).hide();
			} );
			
			
			
			//// submit form
			
			if ( opts.use_jquery_form ) {
				
				// use the jquery form plugin, which handles file uploads
				form.ajaxForm( {
					dataType: opts.data_type,
					url: opts.process_script + '?' + opts.action.substring( 1 ),
					beforeSubmit: function( arr, aform, params ) {
						
						form.find( opts.classes.init ).hide();
						form.find( opts.classes.loading ).show();
						form.find( opts.classes.form_elems ).removeClass( opts.classes.error_field );
						
						var errors = [];
						
						errors = opts.validate( form, errors );
						
						if ( errors.length ) {
							
							form.find( opts.classes.error ).html( formatMsg( errors ) ).show();
							form.find( opts.classes.loading ).hide();
							if ( opts.form_top ) window.location = opts.form_top;
							
							return false;
						}
						
						return true;
					},
					success: function( res ) {
						opts.process( form, res, opts.status );
					},
					error: function() {
						form.find( opts.classes.error ).html( 'An unknown error occurred. Please try again.' ).show();								
						form.find( opts.classes.loading ).hide();
					}
				} );
				
			} else {
				
				form.submit( function() {
					
					form.find( opts.classes.init ).hide();
					form.find( opts.classes.loading ).show();
					form.find( opts.classes.form_elems ).removeClass( opts.classes.error_field );
					
					var errors = [];
					
					errors = opts.validate( form, errors );
					
					if ( errors.length ) {
						
						form.find( opts.classes.error ).html( formatMsg( errors ) ).show();
						form.find( opts.classes.loading ).hide();
						if ( opts.form_top ) window.location = opts.form_top;
						
					} else {
						
						form.find( opts.classes.error ).hide();
						
						// already logged in, so process order
						$.post(
							opts.process_script,
							form.serialize() + opts.action,
							function ( res ) {
								opts.process( form, res, opts.status );
							},
							opts.data_type
						).error( function() {
							form.find( opts.classes.error ).html( 'An unknown error occurred. Please try again.' ).show();								
							form.find( opts.classes.loading ).hide();
						} );
						
					}
					
					return false;
					
				} );
			
			}
			
		} );
		
	};
	
} )( jQuery );
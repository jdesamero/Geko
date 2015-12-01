( function() {
	
	var Geko = this.Geko;
	var Gloc = this.Gloc;
	
	
	var $ = this.jQuery;
	
	
	// main
	
	Gloc.setNamespace( 'ForgotPassword.Family', Backstab.family( {
		
		model: {

			extend: {
				
				defaults: {
					'email': ''
				},
				
				validate: function( attrs ) {
					
					var oParams = this.family.data.params;
					var labels = oParams.labels;
					
					var err = {};
					
					if ( !attrs.email ) {
						err.email = labels[ 104 ];
					} else {
						
						if ( !$.gekoValidateEmail( attrs.email ) ) {
							err.email = labels[ 105 ];					
						}
					}
					
					return ( $.isEmptyObject( err ) ) ? false : err ;
				
				},
				
				
				sync: function( method, model, options ) {
					
					var oParams = this.family.data.params;
					
					// dirty, model or collection may be passed, but not both
					var oData = {
						'_service': 'Gloc_Service_Profile',
						'_action': 'forgot_password'
					};
					
					oData = $.extend( oData, model.toJSON() );
					
					$.post(
						oParams.script.process,
						oData,
						function ( res ) {
							options.success( res );
						},
						'json'
					);
					
				}
				
			}
			
		},
		
		collection: false,
		
		itemView: {
			
			params: { status: Gloc.Status },
			
			extend: {
				
				events: {
					'submit form; click input[type="submit"]': 'send',
					'model:invalid this': 'error'
				},
				
				send: function( e ) {
					
					var _this = this;
					
					var oParams = this.family.data.params;
					var labels = oParams.labels;
					
					this.status.reset().loading( true );
					
					this.model.save( this.getModelDataFromElem(), {
						success: function( oModel, oRes ) {
							
							if ( oRes.error ) {
								
								_this.status.errors( oRes.error_details, oRes.error_msg, oRes.error );							
																
							} else {
								
								_this.$el.hide();
								$( oParams.success_div_sel ).show();
								
							}
							
							_this.status.loading( false );
							
						}
					} );
					
					return false;
				},
				
				error: function( e, oModel, oError ) {

					var oParams = this.family.data.params;
					var labels = oParams.labels;
					
					this.status.errors( oError, labels[ 108 ] ).loading( false );
				}
				
			}
			
		},
		
		listView: false,
		
		formView: false
		
	} ) );	

	
	
	
	//
	Gloc.setNamespace( 'ForgotPassword', {
		
		run: function( oParams ) {
			
			Gloc.ForgotPassword.Family.setData( 'params', oParams );
			
			var oForgotPasswordModel = new Gloc.ForgotPassword.Family.Model();
			
			var oForgotPasswordView = new Gloc.ForgotPassword.Family.ItemView( {
				el: $( oParams.form_sel ),
				model: oForgotPasswordModel
			} );
			
		}
		
	} );
	
	
	
} ).call( this );




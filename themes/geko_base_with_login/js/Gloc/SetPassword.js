( function() {
	
	var Geko = this.Geko;
	var Gloc = this.Gloc;
	
	
	var $ = this.jQuery;
	
	
	// main
	
	Gloc.setNamespace( 'SetPassword.Family', Backstab.family( {
		
		model: {

			extend: {
				
				defaults: {
					'key': '',
					'password': '',
					'confirm_pass': ''
				},
				
				validate: function( attrs ) {
					
					var oParams = this.family.data.params;
					var labels = oParams.labels;
					
					var err = {};
					
					if ( !attrs.password ) {
						err.password = labels[ 107 ];
					} else {
						if ( attrs.password.length < 6 ) {
							err.password = labels[ 108 ];
						} else {
							if ( attrs.password != attrs.confirm_pass ) {
								err.confirm_pass = labels[ 109 ];					
							}
						}
					}
					
					return ( $.isEmptyObject( err ) ) ? false : err ;
				
				},
				
				
				sync: function( method, model, options ) {
					
					var oParams = this.family.data.params;
					
					// dirty, model or collection may be passed, but not both
					var oData = {
						'_service': 'Gloc_Service_Profile',
						'_action': 'set_password'
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
					
					this.status.errors( oError, labels[ 111 ] ).loading( false );
				}
				
			}
			
		},
		
		listView: false,
		
		formView: false
		
	} ) );	

	
	
	
	//
	Gloc.setNamespace( 'SetPassword', {
		
		run: function( oParams ) {
			
			Gloc.SetPassword.Family.setData( 'params', oParams );
			
			var oSetPasswordModel = new Gloc.SetPassword.Family.Model();
			
			var oSetPasswordView = new Gloc.SetPassword.Family.ItemView( {
				el: $( oParams.form_sel ),
				model: oSetPasswordModel
			} );
			
		}
		
	} );
	
	
	
} ).call( this );




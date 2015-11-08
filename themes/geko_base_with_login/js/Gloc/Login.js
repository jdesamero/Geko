( function() {
	
	var Geko = this.Geko;
	var Gloc = this.Gloc;
	
	
	var $ = this.jQuery;
	
	
	// main
	
	Gloc.setNamespace( 'Login.Family', Backstab.family( {
		
		model: {

			extend: {
				
				defaults: {
					'email': '',
					'password': ''
				},
				
				validate: function( attrs ) {
					
					var oParams = this.family.data.params;
					var labels = oParams.labels;
					
					var err = {};
					
					if ( !attrs.email ) {
						err.email = labels[ 113 ];
					} else {
						
						if ( !$.gekoValidateEmail( attrs.email ) ) {
							err.email = labels[ 114 ];					
						}
					}
					
					if ( !attrs.password ) {
						err.password = labels[ 115 ];
					} else {
						if ( attrs.password.length < 6 ) {
							err.password = labels[ 116 ];
						}
					}
					
					return ( $.isEmptyObject( err ) ) ? false : err ;
				
				},
				
				
				sync: function( method, model, options ) {
					
					var oParams = this.family.data.params;
					
					// dirty, model or collection may be passed, but not both
					var oData = {
						'_service': 'Gloc_Service_Profile',
						'_action': 'login'
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
					'submit form; click input[type="submit"]': 'authenticate',
					'model:invalid this': 'error'
				},
				
				authenticate: function( e ) {
					
					var _this = this;
					
					var oParams = this.family.data.params;
					var labels = oParams.labels;
					
					this.status.reset().loading( true );
					
					this.model.save( this.getModelDataFromElem(), {
						success: function( oModel, oRes ) {
							
							if ( oRes.error ) {
								
								_this.status.errors( oRes.error_details, oRes.error_msg, oRes.error );							
																
							} else {
								
								_this.status.success( labels[ 123 ] );
								window.location = oParams.script.curpage;
							}
							
							_this.status.loading( false );
							
						}
					} );
					
					return false;
				},
				
				error: function( e, oModel, oError ) {

					var oParams = this.family.data.params;
					var labels = oParams.labels;
					
					this.status.errors( oError, labels[ 122 ] ).loading( false );
				}
				
			}
			
		},
		
		listView: false,
		
		formView: false
		
	} ) );	

	
	
	
	//
	Gloc.setNamespace( 'Login', {
		
		run: function( oParams ) {
			
			Gloc.Login.Family.setData( 'params', oParams );
			
			var oLoginModel = new Gloc.Login.Family.Model();
			
			var oLoginView = new Gloc.Login.Family.ItemView( {
				el: $( oParams.form_sel ),
				model: oLoginModel
			} );
			
		}
		
	} );
	
	
	
} ).call( this );




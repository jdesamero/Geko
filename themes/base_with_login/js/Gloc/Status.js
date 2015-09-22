( function() {
	
	var Gloc = this.Gloc;
	
	var $ = this.jQuery;
	
	
	// handler of ajax form submission status:
	// loader, notification, and input validation
	Gloc.setNamespace( 'Status', function( eMainElem ) {
		
		this.eMain = eMainElem;
		this.eLoading = null;							// reference to the loading element
		
		this.eError = null;
		this.eSuccess = null;
		
		
		//// accessors
		
		// set main element
		this.setElem = function( eElem ) {
			
			this.eMain = eElem;
						
			return this;
		};
		
		
		
		
		//// helpers
		
		// set up body reference
		this.initBody = function() {
			
			if ( !this.eError ) {
				this.eError = this.eMain.find( '.error' );
			}

			if ( !this.eSuccess ) {
				this.eSuccess = this.eMain.find( '.success' );
			}
			
		};
		
		// get markup of status div
		this.getStatusMarkup = function( sType, sTitle, sMsg ) {
			// sType is currently not used
			return '<div><strong>%s:<\/strong> %s<\/div>'.printf( sTitle, sMsg );
		};
		
		
		//// main methods
		
		
		// reset the status
		this.reset = function() {
			
			this.initBody();
			
			// clear all alerts
			
			this.eMain.find( '.error_field' ).removeClass( 'error_field' );
			this.eMain.find( '.error_msg' ).remove();
			
			this.eError.html( '' );
			this.eSuccess.html( '' );
			
			return this;
		};
		
		
		// set loading
		this.loading = function( mState ) {
			
			if ( !this.eLoading ) {
				this.eLoading = this.eMain.find( '.loading' );
			}
			
			if ( mState ) {
				this.eLoading.show();
			} else {
				this.eLoading.hide();				
			}
			
			return this;
		};
		
		
		
		
		// set a bunch of errors
		this.errors = function( oErrors, sMsg, sTitle ) {
			
			var _this = this;
			
			this.initBody();
			
			this.error( sMsg, sTitle );
			
			
			// attach error message to corresponding fields
			
			if ( oErrors ) {
				
				$.each( oErrors, function( k, v ) {
					
					var eFld = _this.eMain.find( '#%s'.printf( k ) );
					eFld.addClass( 'error_field' );
					
					// var sErrorMsgDiv = '<div class="error_msg">%s<\/div>'.printf( v );
					// eFld.after( sErrorMsgDiv );
					
					_this.eError.append( '<div>%s<\/div>'.printf( v ) );
					
				} );
			
			}
			
			return this;
		};
		
		// set an error
		this.error = function( sMsg, sTitle ) {
			
			var _this = this;
			
			this.initBody();
			
			if ( !sTitle ) sTitle = 'Error';
			
			this.eError.html( '' );			// hack, empty this out
			this.eError.append( this.getStatusMarkup( 'error', sTitle, sMsg ) ).show();
			
			return this;
		};
		
		// set status message
		this.success = function( sMsg, sTitle ) {
			
			var _this = this;
			
			this.initBody();
			
			if ( !sTitle ) sTitle = 'Success';
			
			this.eSuccess.html( '' );			// hack, empty this out
			this.eSuccess.append( this.getStatusMarkup( 'success', sTitle, sMsg ) ).show();
			
			return this;
		};
		
		
		
		
	} );
	
	
} ).call( this );
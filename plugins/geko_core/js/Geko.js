( function() {
	
	// set up the Geko namespace and descendants
	
	var Geko = this.Geko;
	var $ = this.jQuery;
	
	if ( !Geko ) Geko = {};
	
	
	
	// main
	
	$.extend( Geko, {
		
		_setNamespace: function( sNamespace, mValue ) {
			
			// spit into parts
			
			var aParts = sNamespace.split( '.' );
			var mTarget = this;
			var mPrevTarget = null;
			var sLastKey = null;
			
			$.each( aParts, function( i, v ) {
				
				if ( !mTarget[ v ] ) {
					
					// assign empty object
					mTarget[ v ] = {};
				}
				
				mPrevTarget = mTarget;
				mTarget = mTarget[ v ];
				sLastKey = v;
				
			} );
			
			if ( mValue ) {
				
				// assign target's props to value function, if any
				$.each( mTarget, function( k, v ) {
					mValue[ k ] = v;
				} );
				
				// do a switcheroo
				mPrevTarget[ sLastKey ] = mValue;
				
			}
			
		},
		
		setNamespace: function() {
			Geko._setNamespace.apply( Geko, arguments );
		}
		
	} );
	
	
	this.Geko = Geko;
	
} ).call( this );
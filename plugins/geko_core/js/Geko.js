( function() {
	
	// set up the Geko namespace and descendants
	
	var Geko = this.Geko;
	var $ = this.jQuery;
	
	if ( !Geko ) Geko = {};
	
	
	// helpers
	
	var fAssignProps = function( mTarget, oProps ) {
		
		$.each( oProps, function( k, v ) {
			mTarget[ k ] = v;
		} );
		
	};
	
	
	
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
				
				var sTargetType = $.type( mTarget );
				var sValueType = $.type( mValue );
				
				if ( 'object' === sTargetType ) {
					
					if ( 'object' === sValueType ) {
						
						// do a merge
						$.extend( mTarget, mValue );
						
					} else if ( 'function' === sValueType ) {
						
						// assign target's props to value function, if any
						fAssignProps( mValue, mTarget );
						
						// do a switcheroo
						mPrevTarget[ sLastKey ] = mValue;
						
					}
					
				} else if ( 'function' === sTargetType ) {
				
					if ( 'object' === sValueType ) {
						
						// assign value props to target function
						fAssignProps( mTarget, mValue );
						
					} else if ( 'function' === sValueType ) {
						
						// assign target's props to value function, if any
						fAssignProps( mValue, mTarget );
						
						// overwrite the existing function
						mPrevTarget[ sLastKey ] = mValue;
						
					}
				
				}
			
			}
			
		},
		
		setNamespace: function() {
			Geko._setNamespace.apply( Geko, arguments );
		}
		
	} );
	
	
	this.Geko = Geko;
	
} ).call( this );
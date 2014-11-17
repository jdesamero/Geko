( function() {
	
	// set up the Geko namespace and descendants
	
	var $ = this.jQuery;
	
	var Geko = {
		
		_setNamespace: function( sNamespace, oInitTarget ) {
			
			// spit into parts
			
			var aParts = sNamespace.split( '.' );
			var oTarget = oInitTarget;			// start here
			
			$.each( aParts, function( i, v ) {
				
				if ( !oTarget[ v ] ) {
					oTarget[ v ] = {};
				}
				
				oTarget = oTarget[ v ];
				
			} );
			
		},
		
		setNamespace: function( sNamespace ) {
			Geko._setNamespace( sNamespace, Geko );
		}
		
	};
	
	
	this.Geko = Geko;
	
} ).call( this );
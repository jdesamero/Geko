( function() {
	
	// set up the Geko namespace and descendants
	
	var $ = this.jQuery;
	var Geko = {};
	
	Geko.setNamespace = function( sNamespace ) {
		
		// spit into parts
		
		var aParts = sNamespace.split( '.' );
		var oTarget = Geko;		// start here
		
		$.each( aParts, function( i, v ) {
			
			if ( !oTarget[ v ] ) {
				oTarget[ v ] = {};
			}
			
			oTarget = oTarget[ v ];
			
		} );
		
	};
	
	
	this.Geko = Geko;
	
} ).call( this );
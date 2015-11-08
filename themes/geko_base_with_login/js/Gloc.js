( function() {
	
	// set up the Gloc namespace and descendants
	
	var $ = this.jQuery;
	
	var Gloc = {
		
		setNamespace: function() {
			Geko._setNamespace.apply( Gloc, arguments );
		}
		
	};
	
	
	this.Gloc = Gloc;
	
} ).call( this );
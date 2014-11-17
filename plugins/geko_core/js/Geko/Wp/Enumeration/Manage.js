( function() {
	
	var Geko = this.Geko;
	var $ = this.jQuery;
	
	Geko.setNamespace( 'Wp.Enumeration.Manage' );
	
	
	
	//// main family
	
	Geko.Wp.Enumeration.Manage = Backstab.family( {
	
		name: 'context',
		
		model: {
			
			extend: {
				
				fields: Backstab.ModelFields[ 'enum' ]
				
			}
			
		}
		
		
	} );
	
	
	
} ).call( this );
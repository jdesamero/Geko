( function() {
	
	var Geko = this.Geko;
	var $ = this.jQuery;
	
	
	
	//// main family
	
	Geko.setNamespace( 'Wp.Enumeration.Manage', Backstab.family( {
	
		name: 'context',
		
		model: {
			
			extend: {
				
				fields: Backstab.ModelFields[ 'enum' ]
				
			}
			
		}
		
	} ) );
	
	
	
} ).call( this );
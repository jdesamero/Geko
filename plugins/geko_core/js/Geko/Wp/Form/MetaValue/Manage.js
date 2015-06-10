( function() {
	
	var Geko = this.Geko;
	var $ = this.jQuery;
	
	
	
	// meta data value
	
	Geko.setNamespace( 'Wp.Form.MetaValue.Manage', Backstab.family( {
		
		name: 'meta_value',
		
		model: {
		
			extend: {
				
				fields: Backstab.ModelFields[ 'form.meta_value' ]
				
			}
		}
		
	} ) );
	
	
	
} ).call( this );
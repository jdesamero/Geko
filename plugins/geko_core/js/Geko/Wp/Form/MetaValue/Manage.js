;( function ( $ ) {
	
	var Geko = this.Geko;
	var $ = this.jQuery;
	
	Geko.setNamespace( 'Wp.Form.MetaValue.Manage' );
	
	
	
	// meta data value
	
	Geko.Wp.Form.MetaValue.Manage = Backstab.family( {
		
		name: 'meta_value',
		
		model: {
		
			extend: {
				
				fields: Backstab.ModelFields[ 'form.meta_value' ]
				
			}
		}
		
	} );
	
	
	
} )( jQuery );
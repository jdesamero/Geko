( function() {
	
	var Geko = this.Geko;
	var $ = this.jQuery;
	
	
	
	//// item meta value
	
	Geko.setNamespace( 'Wp.Form.ItemMetaValue.Manage', Backstab.family( {
		
		name: 'item_meta_value',
		
		model: {
			
			extend: {
				
				fields: Backstab.ModelFields[ 'form.item_meta_value' ]
				
			}
			
		}
		
	} ) );
	
	
	
} ).call( this );
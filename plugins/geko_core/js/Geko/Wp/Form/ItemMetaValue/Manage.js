;( function ( $ ) {
	
	var Geko = this.Geko;
	var $ = this.jQuery;
	
	Geko.setNamespace( 'Wp.Form.ItemMetaValue.Manage' );
	
	
	
	//// item meta value
	
	Geko.Wp.Form.ItemMetaValue.Manage = Backstab.family( {
		
		name: 'item_meta_value',
		
		model: {
			
			extend: {
				
				fields: Backstab.ModelFields[ 'form.item_meta_value' ]
				
			}
			
		}
		
	} );
	
	
	
} )( jQuery );
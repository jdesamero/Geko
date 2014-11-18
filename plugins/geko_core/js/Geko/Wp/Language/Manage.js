( function() {
	
	var Geko = this.Geko;
	var $ = this.jQuery;
	
	
	
	//
	Geko.setNamespace( 'Wp.Language.Manage', Backstab.family( {
		
		name: 'language',
		
		model: {
			
			extend: {
				
				fields: Backstab.ModelFields[ 'lang' ]
				
			}
			
		},
		
		collection: {
			
			extend: {
				
				getCode: function( iLangId ) {
					return this.findAndGet( 'code', { 'lang_id': iLangId } );
				}
				
			}
			
		}
		
	} ) );
	
	
	
} ).call( this );
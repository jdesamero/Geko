<?php

//
class Geko_Wp_Post_Query_Plugin_Redirect extends Geko_Entity_Query_Plugin
{
	
	
	//
	public function modifyQuery( $oQuery, $aParams ) {
		
		// apply super-class manipulations
		$oQuery = parent::modifyQuery( $oQuery, $aParams );
		
		if ( $aParams[ 'add_tagline_field' ] ) {
			
			$oQuery
				->field( 'tagl.tagline', 'tagline' )
				->joinLeft( '##pfx##postmeta', 'tagl' )
					->on( 'tagl.post_id = p.ID' )
					->on( 'tagl.meta_key = ?', 'Tagline' )
			;
			
		}
		
		return $oQuery;
	
	}
	
	
}




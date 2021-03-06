<?php

//
class Geko_Wp_Post_Query_Plugin_Author extends Geko_Entity_Query_Plugin
{
	
	
	//
	public function modifyQuery( $oQuery, $aParams, $oEntityQuery ) {
		
		// apply super-class manipulations
		$oQuery = parent::modifyQuery( $oQuery, $aParams, $oEntityQuery );
		
		
		if ( $aParams[ 'add_author_field' ] ) {
			
			
			$oQuery
				
				->field( 'pauth.meta_value', 'author' )
				
				->joinLeft( '##pfx##postmeta', 'pauth' )
					->on( 'pauth.post_id = p.ID' )
					->on( 'pauth.meta_key = ?', 'Author' )
				
			;
			
			
		}
		
		
		return $oQuery;
	
	}
	
	
}




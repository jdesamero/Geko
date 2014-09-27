<?php

//
class Geko_Wp_Post_Query_Plugin_Redirect extends Geko_Entity_Query_Plugin
{
	
	
	//
	public function modifyQuery( $oQuery, $aParams ) {
		
		// apply super-class manipulations
		$oQuery = parent::modifyQuery( $oQuery, $aParams );
		
		if ( $aParams[ 'add_redirect_field' ] ) {
			
			$oQuery
				->field( 'redr.redirect', 'redirect' )
				->joinLeft( '##pfx##postmeta', 'redr' )
					->on( 'redr.post_id = p.ID' )
					->on( 'redr.meta_key = ?', 'Redirect' )
			;
			
		}
		
		return $oQuery;
	
	}
	
	
}




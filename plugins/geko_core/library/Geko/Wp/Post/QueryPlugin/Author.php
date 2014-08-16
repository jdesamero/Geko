<?php

//
class Geko_Wp_Post_QueryPlugin_Author extends Geko_Entity_Query_Plugin
{
	
	
	//
	public function modifyQuery( $oQuery, $aParams ) {
		
		global $wpdb;
		
		if ( $aParams[ 'add_author_field' ] ) {
			
			
			$oQuery
				
				->field( 'pauth.meta_value', 'author' )
				
				->joinLeft( $wpdb->postmeta, 'pauth' )
					->on( 'pauth.post_id = p.ID' )
					->on( 'pauth.meta_key = ?', 'Author' )
				
			;
			
			
		}
		
		
		return $oQuery;
	
	}
	
	
}




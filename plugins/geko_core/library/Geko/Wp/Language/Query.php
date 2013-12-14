<?php

// listing
class Geko_Wp_Language_Query extends Geko_Wp_Entity_Query
{
	
	protected $_bUseManageQuery = TRUE;
	
	
	
	//
	public function modifyQuery( $oQuery, $aParams ) {
		
		global $wpdb;
		
		// apply super-class manipulations
		$oQuery = parent::modifyQuery( $oQuery, $aParams );
		
		// lang id
		if ( $aParams[ 'geko_lang_id' ] ) {
			$oQuery->where( 'l.lang_id = ?', $aParams[ 'geko_lang_id' ] );
		}
		
		// lang code/slug
		if ( $aParams[ 'geko_lang_code' ] ) {
			$oQuery->where( 'l.code = ?', $aParams[ 'geko_lang_code' ] );
		}
		
		// apply default sorting
		if ( !isset( $aParams[ 'orderby' ] ) ) {
			$oQuery
				->order( 'l.is_default', 'DESC' )
				->order( 'l.title' )
			;
		}
		
		return $oQuery;
	}
	
	
}



<?php

// listing
class Geko_Wp_Language_String_Query extends Geko_Wp_Entity_Query
{	
	
	protected $_bUseManageQuery = TRUE;
	
	
	
	//
	public function modifyQuery( $oQuery, $aParams ) {
		
		// apply super-class manipulations
		$oQuery = parent::modifyQuery( $oQuery, $aParams );
		
		// str id
		if ( $aParams[ 'str_id' ] ) {
			$oQuery->where( 's.str_id = ?', $aParams[ 'str_id' ] );
		}
		
		// emsg id
		if ( $aParams[ 'lang_id' ] ) {
			$oQuery->where( 's.lang_id = ?', $aParams[ 'lang_id' ] );
		}
				
		return $oQuery;
	}
	
	
}



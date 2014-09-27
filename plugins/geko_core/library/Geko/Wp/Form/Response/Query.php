<?php

// listing
class Geko_Wp_Form_Response_Query extends Geko_Wp_Entity_Query
{	

	protected $_bUseManageQuery = TRUE;
	
	
	//
	public function modifyQuery( $oQuery, $aParams ) {
		
		// apply super-class manipulations
		$oQuery = parent::modifyQuery( $oQuery, $aParams );
		
		// form id
		if ( $aParams[ 'form_id' ] ) {
			$oQuery->where( 'fr.form_id = ?', $aParams[ 'form_id' ] );
		}
		
		return $oQuery;
	}
	
	
}



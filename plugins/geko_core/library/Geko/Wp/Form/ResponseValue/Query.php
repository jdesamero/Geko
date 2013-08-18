<?php

// listing
class Geko_Wp_Form_ResponseValue_Query extends Geko_Wp_Entity_Query
{	

	protected $_bUseManageQuery = TRUE;
	
	
	//
	public function modifyQuery( $oQuery, $aParams ) {
		
		global $wpdb;
		
		// apply super-class manipulations
		$oQuery = parent::modifyQuery( $oQuery, $aParams );
		
		// form id
		if ( $aParams[ 'fmrsp_id' ] ) {
			$oQuery->where( 'frv.fmrsp_id = ?', $aParams[ 'fmrsp_id' ] );
		}
		
		return $oQuery;
	}
	
	
	
}



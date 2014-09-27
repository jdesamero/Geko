<?php

// listing
class Geko_Wp_Form_Section_Query extends Geko_Wp_Entity_Query
{	
	
	protected $_bUseManageQuery = TRUE;
	
	
	
	//
	public function modifyQuery( $oQuery, $aParams ) {
		
		// apply super-class manipulations
		$oQuery = parent::modifyQuery( $oQuery, $aParams );
		
		// fmsec id
		if ( $aParams[ 'fmsec_id' ] ) {
			$oQuery->where( 'fs.fmsec_id = ?', $aParams[ 'fmsec_id' ] );
		}
		
		// form id
		if ( $aParams[ 'form_id' ] ) {
			$oQuery->where( 'fs.form_id = ?', $aParams[ 'form_id' ] );
		}
		
		return $oQuery;
	}
	
	
}



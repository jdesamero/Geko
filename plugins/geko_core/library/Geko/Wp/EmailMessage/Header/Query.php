<?php

// listing
class Geko_Wp_EmailMessage_Header_Query extends Geko_Wp_Entity_Query
{	
	
	protected $_bUseManageQuery = TRUE;
	
	
	
	//
	public function modifyQuery( $oQuery, $aParams ) {
		
		global $wpdb;
		
		// apply super-class manipulations
		$oQuery = parent::modifyQuery( $oQuery, $aParams );
		
		// hdr_id
		if ( $aParams[ 'hdr_id' ] ) {
			$oQuery->where( 'h.hdr_id = ?', $aParams[ 'hdr_id' ] );
		}
		
		// emsg id
		if ( $aParams[ 'emsg_id' ] ) {
			$oQuery->where( 'h.emsg_id = ?', $aParams[ 'emsg_id' ] );
		}
		
		return $oQuery;
	}
	
	
}



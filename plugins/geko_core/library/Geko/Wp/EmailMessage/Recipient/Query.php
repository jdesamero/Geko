<?php

// listing
class Geko_Wp_EmailMessage_Recipient_Query extends Geko_Wp_Entity_Query
{	
	
	protected $_bUseManageQuery = TRUE;
	
	
	
	//
	public function modifyQuery( $oQuery, $aParams ) {
		
		global $wpdb;
		
		// apply super-class manipulations
		$oQuery = parent::modifyQuery( $oQuery, $aParams );
		
		// rcpt id
		if ( $aParams[ 'rcpt_id' ] ) {
			$oQuery->where( 'r.rcpt_id = ?', $aParams[ 'rcpt_id' ] );
		}
		
		// emsg id
		if ( $aParams[ 'emsg_id' ] ) {
			$oQuery->where( 'r.emsg_id = ?', $aParams[ 'emsg_id' ] );
		}
		
		// active
		if ( $aParams[ 'is_active' ] ) {
			$oQuery->where( '1 = r.active' );
		}
				
		return $oQuery;
	}
	
	
}



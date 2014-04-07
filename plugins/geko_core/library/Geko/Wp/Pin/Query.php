<?php

// listing
class Geko_Wp_Pin_Query extends Geko_Wp_Entity_Query
{
	
	protected $_bUseManageQuery = TRUE;
	
	
	
	//
	public function modifyQuery( $oQuery, $aParams ) {
		
		global $wpdb;
		
		// apply super-class manipulations
		$oQuery = parent::modifyQuery( $oQuery, $aParams );
		
		
		
		
		// pin id
		if ( isset( $aParams[ 'pin_id' ] ) ) {
			$oQuery->where( 'p.pin_id = ?', $aParams[ 'pin_id' ] );
		}
		
		// pin itself (slug)
		if ( isset( $aParams[ 'pin' ] ) ) {
			$oQuery->where( 'p.pin = ?', $aParams[ 'pin' ] );
		}
		
		
		
		
		return $oQuery;
	}
	
	
}
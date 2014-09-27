<?php

// listing
class Geko_Wp_Booking_Schedule_Time_Query extends Geko_Wp_Entity_Query
{
	
	protected $_bUseManageQuery = TRUE;
	
	
	
	//
	public function modifyQuery( $oQuery, $aParams ) {
		
		// apply super-class manipulations
		$oQuery = parent::modifyQuery( $oQuery, $aParams );
		
		
		
		// bksctm id
		if ( $aParams[ 'bksctm_id' ] ) {
			$oQuery->where( 'bst.bksctm_id = ?', $aParams[ 'bksctm_id' ] );
		}
		
		// bksch id
		if ( $aParams[ 'bksch_id' ] ) {
			$oQuery->where( 'bst.bksch_id = ?', $aParams[ 'bksch_id' ] );
		}
				
		return $oQuery;
	}
	
	
}



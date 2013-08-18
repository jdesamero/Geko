<?php

// listing
class Geko_Wp_Booking_Schedule_Time_Query extends Geko_Wp_Entity_Query
{
	//
	public function modifyQuery( $oQuery, $aParams ) {
		global $wpdb;
		
		// apply super-class manipulations
		$oQuery = parent::modifyQuery( $oQuery, $aParams );
		
		$oQuery
			->field( 'bst.bksctm_id' )
			->field( 'bst.bksch_id' )
			->field( 'bst.weekday_id' )
			->field( 'bst.time_start' )
			->field( 'bst.time_end' )
			->from( $wpdb->geko_bkng_schedule_time, 'bst' )
		;
		
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



<?php

// listing
class Geko_Wp_Booking_Transaction_Query extends Geko_Wp_Entity_Query
{
	//
	public function modifyQuery( $oQuery, $aParams ) {
		global $wpdb;
		
		// apply super-class manipulations
		$oQuery = parent::modifyQuery( $oQuery, $aParams );
		
		$oQuery
			->field( 'btr.bktrn_id' )
			->field( 'btr.transaction_type_id' )
			->field( 'btr.status_id' )
			->field( 'btr.is_test' )
			->field( 'btr.bkitm_id' )
			->field( 'btr.details' )
			->field( 'btr.units' )
			->field( 'btr.unit_cost' )
			->field( 'btr.discount' )
			->field( 'btr.tax' )
			->field( 'btr.amount' )
			->field( 'btr.user_id' )
			->field( 'btr.date_created' )
			->from( $wpdb->geko_bkng_transaction, 'btr' )
		;
		
		return $oQuery;
	}
	
	
}



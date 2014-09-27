<?php

// listing
class Geko_Wp_Booking_Request_Query extends Geko_Wp_Entity_Query
{
	
	protected $_bUseManageQuery = TRUE;
	
	
	
	//
	public function modifyQuery( $oQuery, $aParams ) {
		
		// apply super-class manipulations
		$oQuery = parent::modifyQuery( $oQuery, $aParams );
		
		$oQuery
						
			->field( 'u.user_email', 'email' )
			->joinLeft( '##pfx##users', 'u' )
				->on( 'u.ID = brq.user_id' )
			
			->fieldKvp( 'um1.meta_value', 'first_name' )
			->fieldKvp( 'um2.meta_value', 'last_name' )
			->joinLeftKvp( '##pfx##usermeta', 'um*' )
				->on( 'um*.user_id = u.ID' )
				->on( 'um*.meta_key = ?', '*' )
			
		;
		
		
		// bkreq id
		if ( $aParams[ 'bkreq_id' ] ) {
			$oQuery->where( 'brq.bkreq_id = ?', $aParams[ 'bkreq_id' ] );
		}
		
		// bkitm id
		if ( $aParams[ 'bkitm_id' ] ) {
			$oQuery->where( 'brq.bkitm_id = ?', $aParams[ 'bkitm_id' ] );
		}
		
		//
		if ( $aParams[ 'user_id' ] ) {
			$oQuery->where( 'brq.user_id = ?', $aParams[ 'user_id' ] );
		}
		
		return $oQuery;
	}
	
	
}



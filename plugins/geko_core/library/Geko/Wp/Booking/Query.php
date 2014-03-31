<?php

// listing
class Geko_Wp_Booking_Query extends Geko_Wp_Entity_Query
{
	
	protected $_bUseManageQuery = TRUE;
	
	
	
	//
	public function getDefaultParams() {
		return $this->setWpQueryVars( 'paged', 'posts_per_page' );
	}
	
	
	
	//
	public function modifyQuery( $oQuery, $aParams ) {
		
		global $wpdb;
		
		// apply super-class manipulations
		$oQuery = parent::modifyQuery( $oQuery, $aParams );
		
		
		
		// bkng id
		if ( $aParams[ 'geko_bkng_id' ] ) {
			$oQuery->where( 'b.bkng_id = ?', $aParams[ 'geko_bkng_id' ] );
		}

		// bkng slug
		if ( $aParams[ 'geko_bkng_slug' ] ) {
			$oQuery->where( 'b.bkng_slug = ?', $aParams[ 'geko_bkng_slug' ] );
		}
		
		
		return $oQuery;
	}
	
	
}



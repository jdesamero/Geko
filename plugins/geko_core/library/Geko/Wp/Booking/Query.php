<?php

// listing
class Geko_Wp_Booking_Query extends Geko_Wp_Entity_Query
{
	//
	public function getDefaultParams() {
		return $this->setWpQueryVars( 'paged', 'posts_per_page' );
	}
	
	
	
	//
	public function modifyQuery( $oQuery, $aParams ) {
		
		global $wpdb;
		
		// apply super-class manipulations
		$oQuery = parent::modifyQuery( $oQuery, $aParams );
		
		$oQuery
			->field( 'b.bkng_id' )
			->field( 'b.name' )
			->field( 'b.slug' )
			->field( 'b.description' )
			->field( 'b.date_created' )
			->field( 'b.date_modified' )
			->from( $wpdb->geko_booking, 'b' )
		;
		
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



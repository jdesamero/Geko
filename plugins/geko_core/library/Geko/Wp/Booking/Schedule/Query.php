<?php

// listing
class Geko_Wp_Booking_Schedule_Query extends Geko_Wp_Entity_Query
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
			
			->field( 'bs.bksch_id' )
			->field( 'bs.bkng_id' )
			->field( 'bs.name' )
			->field( 'bs.slug' )
			->field( 'bs.description' )
			->field( 'bs.date_start' )
			->field( 'bs.date_end' )
			->field( 'bs.unit' )
			->field( 'bs.cost' )
			->field( 'bs.slots' )
			->field( 'bs.booking_type' )
			->field( 'bs.date_created' )
			->field( 'bs.date_modified' )

			->field( 'b.name', 'booking_name' )
			
			->from( $wpdb->geko_bkng_schedule, 'bs' )
			->joinLeft( $wpdb->geko_booking, 'b' )
				->on( 'b.bkng_id = bs.bkng_id' )
			
		;
		
		
		// $aParams[ 'add_extra_fields' ] is obsolete
		
		//
		if ( $sDate = $aParams[ 'after_date' ] ) {
			// convert date to MySQL timestamp
			$sDbTs = Geko_Db_Mysql::getTimestamp( strtotime( $sDate ) );
			$oQuery->where( 'bs.date_end > ?', $sDbTs );
		}
		
		
		// bksch id
		if ( $aParams[ 'geko_bksch_id' ] ) {
			$oQuery->where( 'bs.bksch_id = ?', $aParams[ 'geko_bksch_id' ] );
		}

		// bksch slug
		if ( $aParams[ 'geko_bksch_slug' ] ) {
			$oQuery->where( 'bs.bksch_slug = ?', $aParams[ 'geko_bksch_slug' ] );
		}
		
		// parent id
		if ( $aParams[ 'parent_id' ] ) {
			$oQuery->where( 'bs.bkng_id = ?', $aParams[ 'parent_id' ] );
		}
		
		return $oQuery;
	}
	
	
}



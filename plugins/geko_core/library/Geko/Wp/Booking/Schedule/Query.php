<?php

// listing
class Geko_Wp_Booking_Schedule_Query extends Geko_Wp_Entity_Query
{
	
	protected $_bUseManageQuery = TRUE;
	
	
	
	//
	public function getDefaultParams() {
		return $this->setWpQueryVars( 'paged', 'posts_per_page' );
	}
	
	
	
	//
	public function modifyQuery( $oQuery, $aParams ) {
		
		$oDb = Geko_Wp::get( 'db' );
		
		// apply super-class manipulations
		$oQuery = parent::modifyQuery( $oQuery, $aParams );
		
		$oQuery
			
			->field( 'b.name', 'booking_name' )
			
			->joinLeft( '##pfx##geko_booking', 'b' )
				->on( 'b.bkng_id = bs.bkng_id' )
			
		;
		
		
		// $aParams[ 'add_extra_fields' ] is obsolete
		
		//
		if ( $sDate = $aParams[ 'after_date' ] ) {
			// convert date to MySQL timestamp
			$sDbTs = $oDb->getTimestamp( strtotime( $sDate ) );
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



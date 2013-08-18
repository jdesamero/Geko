<?php

// listing
class Geko_Wp_Booking_Item_Query extends Geko_Wp_Entity_Query
{
	//
	public function modifyQuery( $oQuery, $aParams ) {
		
		global $wpdb;
		
		// apply super-class manipulations
		$oQuery = parent::modifyQuery( $oQuery, $aParams );
		
		// determine the latest successful transaction
		$oTrMg = Geko_Wp_Booking_Transaction_Manage::getInstance();
		
		$oStQuery = $oTrMg->getSlotsTakenQuery();										// all slots taken
		
		
		
		// main query
		
		$oQuery
			
			->field( 'bsi.bkitm_id' )
			->field( 'bsi.bksch_id' )
			->field( 'bsi.date_item' )
			->field( 'bsi.time_start' )
			->field( 'bsi.time_end' )
			->field(
				"STR_TO_DATE( CONCAT( DATE_FORMAT( bsi.date_item, '%Y-%m-%d' ), ' ', bsi.time_start ), '%Y-%m-%d %l:%i %p' )",
				'datetime_start'
			)
			
			->field( 'b.name', 'booking_name' )
			
			->field( 'bs.name', 'schedule_name' )
			->field( 'bs.booking_type' )
			->field( 'bs.unit' )
			->field( 'bs.cost' )
			->field( 'bs.slots' )
			
			->field( 'bst.slots_taken' )
			
			
			->from( $wpdb->geko_bkng_item, 'bsi' )
			->joinLeft( $wpdb->geko_bkng_schedule, 'bs' )
				->on( 'bs.bksch_id = bsi.bksch_id' )
			->joinLeft( $wpdb->geko_booking, 'b' )
				->on( 'b.bkng_id = bs.bkng_id' )
			->joinLeft( $oStQuery, 'bst' )
				->on( 'bst.bkitm_id = bsi.bkitm_id' )
				
		;
		
		// $aParams[ 'add_extra_fields' ] is obsolete
		
		
		// bksctm id
		if ( $iBkitmId = Geko_String::coalesce( $aParams[ 'bkitm_id' ], $aParams[ 'geko_bkitm_id' ] ) ) {
			$oQuery->where( 'bsi.bkitm_id = ?', $iBkitmId );
		}
		
		// bksch id
		if ( $aParams[ 'bksch_id' ] ) {
			$oQuery->where( 'bsi.bksch_id = ?', $aParams[ 'bksch_id' ] );
		}
		
		// bkng id
		if ( $aParams[ 'bkng_id' ] ) {
			$oQuery->where( 'bs.bkng_id = ?', $aParams[ 'bkng_id' ] );
		}
		
		//
		if ( $sDate = $aParams[ 'date' ] ) {
			// convert date to MySQL timestamp
			$sDbTs = Geko_Db_Mysql::getTimestamp( strtotime( $sDate ) );
			$oQuery->where( 'bsi.date_item = ?', $sDbTs );
		}

		//
		if ( $sDate = $aParams[ 'after_date' ] ) {
			// convert date to MySQL timestamp
			$sDbTs = Geko_Db_Mysql::getTimestamp( strtotime( $sDate ) );
			$oQuery->where( 'bsi.date_item > ?', $sDbTs );
		}
		
		//
		if ( $iUserId = $aParams[ 'user_id' ] ) {
			
			$oStPuQuery = $oTrMg->getSlotsTakenQuery( array( 'per_user' => TRUE ) );
			
			$oQuery
				
				->field( 'bstpu.slots_taken', 'slots_taken_by_user' )
				->joinLeft( $oStPuQuery, 'bstpu' )
					->on( 'bstpu.bkitm_id = bsi.bkitm_id' )	
					->on( 'bstpu.user_id = ?', $iUserId )
				
				->field( 'IF( brq.user_id IS NOT NULL, 1, 0 )', 'notify_current_user' )
				->joinLeft( $wpdb->geko_bkng_request, 'brq' )
					->on( 'brq.bkitm_id = bsi.bkitm_id' )
					->on( 'brq.user_id = ?', $iUserId )
								
			;
			
			if ( $aParams[ 'user_items_only' ] ) {
				$oQuery
					->field( 'bstpu.user_id' )
					->where( '( bstpu.slots_taken IS NOT NULL ) AND ( bstpu.slots_taken > 0 )' )
					->where( 'bstpu.user_id = ?', $iUserId )
				;
			}
			
			if ( $aParams[ 'notify_items_only' ] ) {
				$oQuery
					->field( 'brq.user_id' )
					->where( 'brq.user_id = ?', $iUserId )
				;
			}
			
		}
		
		
		
		// reminders
		if (
			( $bOneWeek = $aParams[ 'reminder-one-week' ] ) || 
			( $bThreeDay = $aParams[ 'reminder-three-day' ] ) || 
			( $bOneDay = $aParams[ 'reminder-one-day' ] )
		) {
			
			$oStPuQuery = $oTrMg->getSlotsTakenQuery( array( 'per_user' => TRUE ) );
			
			$sUserMetaPrefix = Wp_Member_Meta::getInstance()->getPrefixWithSep();
			
			$iTodayTs = strtotime( date( 'Y-m-d' ) );
			$iDaySecs = 60 * 60 * 24;

			if ( $bOneWeek ) {
				$sMetaKey = $sUserMetaPrefix . 'reminder_week_before';
				$sDate = Geko_Db_Mysql::getTimestamp( $iTodayTs + ( 7 * $iDaySecs ) );
			} elseif ( $bThreeDay ) {
				$sMetaKey = $sUserMetaPrefix . 'reminder_three_days_before';
				$sDate = Geko_Db_Mysql::getTimestamp( $iTodayTs + ( 3 * $iDaySecs ) );
			} elseif ( $bOneDay ) {
				$sMetaKey = $sUserMetaPrefix . 'reminder_one_day_before';			
				$sDate = Geko_Db_Mysql::getTimestamp( $iTodayTs + ( 1 * $iDaySecs ) );
			}
			
			$oQuery
				
				->field( 'u.user_email' )
				->field( 'umfn.meta_value', 'user_first_name' )
				->field( 'umln.meta_value', 'user_last_name' )
				
				->joinLeft( $oStPuQuery, 'bstpu' )
					->on( 'bstpu.bkitm_id = bsi.bkitm_id' )	
				->joinLeft( $wpdb->users, 'u' )
					->on( 'u.ID = bstpu.user_id' )
				->joinLeft( $wpdb->usermeta, 'umfn' )
					->on( 'umfn.user_id = u.ID' )
					->on( 'umfn.meta_key = ?', 'first_name' )
				->joinLeft( $wpdb->usermeta, 'umln' )
					->on( 'umln.user_id = u.ID' )
					->on( 'umln.meta_key = ?', 'last_name' )
					
				->joinLeft( $wpdb->usermeta, 'um1' )
					->on( 'um1.user_id = u.ID' )
					->on( 'um1.meta_key = ?', $sMetaKey )
				
				->where( 'bsi.date_item = ?', $sDate )
				->where( " ( um1.meta_value != '' ) AND ( um1.meta_value IS NOT NULL ) " )
				->where( '( bstpu.slots_taken IS NOT NULL ) AND ( bstpu.slots_taken > 0 )' )
				
			;
			
		}

		
		
		return $oQuery;
		
	}
	
	
}



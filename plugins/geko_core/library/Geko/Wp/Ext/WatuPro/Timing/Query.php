<?php

//
class Geko_Wp_Ext_WatuPro_Timing_Query extends Geko_Wp_Entity_Query
{
	
	
	//// static methods for re-use
	
	// pseudo-views
	
	//
	public static function getTimingAggregateQuery() {
		
		$oQuery = new Geko_Sql_Select();
		$oQuery
			
			->field( '_agg_tm.taking_id' )
			
			->field( 'MAX( _agg_tm.ID )', 'max_id' )
			
			->field( 'SUM(
				IF(
					_agg_tm.resume_time != 0,
					UNIX_TIMESTAMP( _agg_tm.resume_time ) - UNIX_TIMESTAMP( _agg_tm.pause_time ),
					0
				)
			)', 'pause_interval' )
			
			->field( 'COUNT(*)', 'num_timings' )
			->field( 'SUM( IF( _agg_tm.resume_time != 0, 1, 0 ) )', 'num_complete' )
			
			
			
			->from( '##pfx##watupro_timing', '_agg_tm' )
			
			->group( '_agg_tm.taking_id' )
		;
		
		return $oQuery;
	}
	
	
	
	//// main methods
	
	//
	public function modifyQuery( $oQuery, $aParams ) {
		
		// apply super-class manipulations
		$oQuery = parent::modifyQuery( $oQuery, $aParams );
		
		
		$bAddTakenExamsFields = $aParams[ 'add_taken_exams_fields' ];
		
		// make wp_watupro_taken_exams table the base table
		if ( $bAddTakenExamsFields ) {
			
			$oQuery
				
				->field( 'tk.user_id' )
				->field( 'tk.exam_id' )
				->field( 'tk.in_progress' )
				
				->field( 'tk.start_time' )
				->field( 'tk.end_time' )
				
				->from( '##pfx##watupro_taken_exams', 'tk' )
			;
			
		}

		
		// aggregate mode
		if ( $aParams[ 'aggregate_mode' ] ) {
			
			// group by taking_id
			
			$oAggregateQuery = self::getTimingAggregateQuery();
			
			// main query
			$oQuery
				->field( 'tm.max_id' )
				->field( 'tm.pause_interval' )
				->field( 'tm.num_timings' )				
				->field( 'tm.num_complete' )
			;
			
			//// This needs to be done, in case timings table is empty
			
			// from or join?
			if ( $bAddTakenExamsFields ) {

				$oQuery
					->field( 'tk.ID', 'taking_id' )					
					->joinLeft( $oAggregateQuery, 'tm' )
						->on( 'tk.ID = tm.taking_id' )
				;
			
			} else {

				$oQuery
					->field( 'tm.taking_id' )
					->from( $oAggregateQuery, 'tm' )
				;
			}
			
			
			$oQuery
				
				->field( 'tmx.pause_time', 'max_pause_time' )
				->field( 'tmx.resume_time', 'max_resume_time' )
				
				->joinLeft( '##pfx##watupro_timing', 'tmx' )
					->on( 'tmx.ID = tm.max_id' )			
			;
			
		} else {
			
			// main query
			$oQuery
				
				->field( 'tm.ID' )
				->field( 'tm.pause_time' )
				->field( 'tm.resume_time' )
				
				->from( '##pfx##watupro_timing', 'tm' )
				
			;
			
			//// This needs to be done, in case timings table is empty
			
			// from or join?
			if ( $bAddTakenExamsFields ) {

				$oQuery
					->field( 'tk.ID', 'taking_id' )					
					->joinLeft( '##pfx##watupro_timing', 'tm' )
						->on( 'tk.ID = tm.taking_id' )
				;
			
			} else {

				$oQuery
					->field( 'tm.taking_id' )
					->from( '##pfx##watupro_timing', 'tm' )
				;
			}
			
		}
		
		
		// taking_id was supplied
		if ( $iTakingId = intval( $aParams[ 'taking_id' ] ) ) {
			
			if ( $bAddTakenExamsFields ) {
				$oQuery->where( 'tk.ID = ?', $iTakingId );			
			} else {
				$oQuery->where( 'tm.taking_id = ?', $iTakingId );
			}
		}
		
		
		
		return $oQuery;
	}
	
	
}




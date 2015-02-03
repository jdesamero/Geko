<?php

//
class Geko_Wp_Ext_WatuPro_Timing_Query extends Geko_Wp_Entity_Query
{
	
	
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
				
				->field( 'UNIX_TIMESTAMP( tk.start_time )', 'start_time_ts' )
				->field( 'UNIX_TIMESTAMP( tk.end_time )', 'end_time_ts' )
				
				->from( '##pfx##watupro_taken_exams', 'tk' )
			;
			
		}

		
		// aggregate mode
		if ( $aParams[ 'aggregate_mode' ] ) {
			
			// group by taking_id
			
			$oAggregateQuery = new Geko_Sql_Select();
			$oAggregateQuery
				
				->field( 'tm1.taking_id' )
				->field( 'MAX( tm1.ID )', 'max_id' )
				->field( 'COUNT(*)', 'num_timings' )
				
				->from( '##pfx##watupro_timing', 'tm1' )
				
				->group( 'tm1.taking_id' )
			;
			
			// main query
			$oQuery
				->field( 'tm.taking_id' )
				->field( 'tm.max_id' )
				->field( 'tm.num_timings' )				
			;
			
			// from or join?
			if ( $bAddTakenExamsFields ) {

				$oQuery
					->joinLeft( $oAggregateQuery, 'tm' )
						->on( 'tk.ID = tm.taking_id' )
				;
			
			} else {

				$oQuery->from( $oAggregateQuery, 'tm' );
			}
			
			
			$oQuery
				
				->field( 'tmx.pause_time', 'max_pause_time' )
				->field( 'tmx.resume_time', 'max_resume_time' )
				
				->field( 'UNIX_TIMESTAMP( tmx.pause_time )', 'max_pause_time_ts' )
				->field( 'UNIX_TIMESTAMP( tmx.resume_time )', 'max_resume_time_ts' )
				
				->joinLeft( '##pfx##watupro_timing', 'tmx' )
					->on( 'tmx.ID = tm.max_id' )			
			;
			
		} else {
			
			// main query
			$oQuery
				
				->field( 'tm.ID' )
				->field( 'tm.taking_id' )
				->field( 'tm.pause_time' )
				->field( 'tm.resume_time' )
				
				->from( '##pfx##watupro_timing', 'tm' )
				
			;
			
			// from or join?
			if ( $bAddTakenExamsFields ) {

				$oQuery
					->joinLeft( '##pfx##watupro_timing', 'tm' )
						->on( 'tk.ID = tm.taking_id' )
				;
			
			} else {

				$oQuery->from( '##pfx##watupro_timing', 'tm' );
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




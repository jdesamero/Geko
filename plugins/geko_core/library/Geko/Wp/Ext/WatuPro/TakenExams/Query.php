<?php

//
class Geko_Wp_Ext_WatuPro_TakenExams_Query extends Geko_Wp_Entity_Query
{
	
	
	//
	public function modifyQuery( $oQuery, $aParams ) {
		
		// apply super-class manipulations
		$oQuery = parent::modifyQuery( $oQuery, $aParams );
		
	
		$oQuery
			
			// taken_exams
			
			->field( 't.ID' )
			->field( 't.percent_correct' )
			->field( 't.user_id' )
			->field( 't.points' )
			->field( 't.exam_id' )
			
			->field( 't.start_time' )
			->field( 't.end_time' )
			
			->field( 't.date', 'date_taken' )
			
			->field( 't.in_progress' )
			
			
			// master
			
			->field( 'm.name' )
			->field( 'm.times_to_take' )
			
			
			->from( '##pfx##watupro_taken_exams', 't' )
			
			->joinLeft( '##pfx##watupro_master', 'm' )
				->on( 'm.ID = t.exam_id' )
				
		;
		
		
		// exam urls
		if ( $aParams[ 'add_exam_post_id_field' ] ) {
			
			$oQuery
				
				->field( 'po.ID', 'exam_post_id' )
	
				->joinLeft( '##pfx##posts', 'po' )
					->on( "po.post_content LIKE CONCAT( '%[watupro ', m.ID, ']%' )" )
					->on( 'po.post_status = ?', 'publish' )
			
			;
			
		}
		
		
		// add timing calculation fields
		if ( $aParams[ 'add_timing_calculation_fields' ] ) {
			
			//// swiped from Geko_Wp_Ext_WatuPro_Timing_Query
			
			// group by taking_id
			
			$oAggregateQuery = Geko_Wp_Ext_WatuPro_Timing_Query::getTimingAggregateQuery();
			
			$oQuery
				
				->field( 'tm.max_id' )
				->field( 'tm.pause_interval' )
				->field( 'tm.num_timings' )				
				->field( 'tm.num_complete' )
				
				->field( 'tmx.pause_time', 'max_pause_time' )
				->field( 'tmx.resume_time', 'max_resume_time' )
				
				->joinLeft( $oAggregateQuery, 'tm' )
					->on( 'tm.taking_id = t.ID' )
				
				->joinLeft( '##pfx##watupro_timing', 'tmx' )
					->on( 'tmx.ID = tm.max_id' )			
			;
			
			
		}
		
		
		// taking_id provided
		if ( $iTakingId = intval( $aParams[ 'taking_id' ] ) ) {
			
			$oQuery->where( 't.ID = ?', $iTakingId );
		}
		
		
		// user_id provided
		if ( $iUserId = intval( $aParams[ 'user_id' ] ) ) {
			
			$oQuery->where( 't.user_id = ?', $iUserId );
		}
		
		
		// exam_id provided
		if ( $iExamId = intval( $aParams[ 'exam_id' ] ) ) {
			
			$oQuery->where( 't.exam_id = ?', $iExamId );
		}
		
		
		
		return $oQuery;
	}
	
	
}
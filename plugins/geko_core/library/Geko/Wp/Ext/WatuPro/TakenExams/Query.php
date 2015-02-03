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
			
			->field( 't.date', 'date_taken' )
			->field( 'UNIX_TIMESTAMP( t.date )', 'date_taken_ts' )
			
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
<?php

//
class Geko_Wp_Ext_WatuPro_Master_Query extends Geko_Wp_Entity_Query
{
	
	//
	public function modifyQuery( $oQuery, $aParams ) {
		
		// apply super-class manipulations
		$oQuery = parent::modifyQuery( $oQuery, $aParams );
		
		$oQuery
			
			->field( 'm.ID' )
			->field( 'm.name' )
			->field( 'm.fee' )
			
			->field( 'm.is_scheduled' )
			
			->field( 'm.schedule_from' )
			->field( 'm.schedule_to' )
			
			->field( 'UNIX_TIMESTAMP( m.schedule_from )', 'schedule_from_ts' )
			->field( 'UNIX_TIMESTAMP( m.schedule_to )', 'schedule_to_ts' )
			
			->field( 'm.times_to_take' )
			
			->from( '##pfx##watupro_master', 'm' )		
		
		;
		
		// exam_id was provided
		if ( $iExamId = intval( $aParams[ 'exam_id' ] ) ) {
			$oQuery->where( 'm.ID = ?', $iExamId );
		}
		
		// taking_id was provided
		if ( $iTakingId = intval( $aParams[ 'taking_id' ] ) ) {
			
			$oQuery
				->field( 't.user_id' )
				->joinLeft( '##pfx##watupro_taken_exams', 't' )
					->on( 't.exam_id = m.ID' )
				->where( 't.ID = ?', $iTakingId )
			;
						
			// get the number of correctly answered questions in this exam
			if ( $aParams[ 'add_correct_questions_field' ] ) {
				
				$oCorrectQuestionsQuery = new Geko_Sql_Select();
				$oCorrectQuestionsQuery
					->field( 'COUNT(*)', 'correct_questions' )
					->from( '##pfx##watupro_student_answers', 'sa' )
					->where( 'sa.taking_id = t.ID' )
					->where( 'sa.is_correct = 1' )
				;
				
				$oQuery->field( $oCorrectQuestionsQuery, 'correct_questions' );
				
			}

		}
		
		
		// get the number of questions in this exam
		if ( $aParams[ 'add_num_questions_field' ] ) {
			
			$oNumQuestionsQuery = new Geko_Sql_Select();
			$oNumQuestionsQuery
				->field( 'COUNT(*)', 'num_questions' )
				->from( '##pfx##watupro_question', 'q' )
				->where( 'q.exam_id = m.ID' )
			;
			
			$oQuery->field( $oNumQuestionsQuery, 'num_questions' );
			
		}
		
		
		// filter exams purchased by user
		if ( $iUserId = intval( $aParams[ 'user_id' ] ) ) {
			
			// use sub-query to ensure uniqueness of exam id
			$oPaidQuery = new Geko_Sql_Select();
			$oPaidQuery
				
				->field( 'p1.exam_id' )
				->field( 'p1.user_id' )
				->field( 'COUNT(*)', 'times_purchased' )
				
				->from( '##pfx##watupro_payments', 'p1' )
				
				->where( 'p1.status = ?', 'completed' )
				
				->group( 'p1.exam_id' )
				->group( 'p1.user_id' )
			;
			
			$oQuery
				->field( 'p.times_purchased' )
				->joinLeft( $oPaidQuery, 'p' )
					->on( 'p.exam_id = m.ID' )
				->where( 'p.times_purchased > 0' )
				->where( 'p.user_id = ?', $iUserId )
			;
			
		}
		
		
		// how many times was a test/exam taken
		if ( $aParams[ 'add_times_taken_field' ] ) {
			
			$oTimesTakenQuery = new Geko_Sql_Select();
			$oTimesTakenQuery
				
				->field( 't2.user_id' )
				->field( 't2.exam_id' )
				->field( 'COUNT(*)', 'times_taken' )
				->field( 'MAX( t2.ID )', 'last_taken_id' )
				
				->from( '##pfx##watupro_taken_exams', 't2' )
				
				->group( 't2.user_id' )
				->group( 't2.exam_id' )
			;
			
			$oQuery
				->field( 'tt.times_taken' )
				->joinLeft( $oTimesTakenQuery, 'tt' )
					->on( 'tt.exam_id = m.ID' )
			;
			
			if ( $iTakingId ) {
				$oQuery->on( 'tt.user_id = t.user_id' );
			}

			if ( $iUserId ) {
				$oQuery->on( 'tt.user_id = p.user_id' );
			}
			
			// get the status of the last taken exam
			$oQuery
				->field( 'lt.in_progress' )
				->joinLeft( '##pfx##watupro_taken_exams', 'lt' )
					->on( 'lt.ID = tt.last_taken_id' )
			;
			
		}
		
		
		// get the URL of the exam
		if ( $aParams[ 'add_exam_url_field' ] ) {
			
			$oQuery
				->field( 'po.ID', 'exam_post_id' )
				->joinLeft( '##pfx##posts', 'po' )
					->on( "po.post_content LIKE CONCAT( '%[watupro ', m.ID, ']%' )" )
					->on( 'po.post_status = ?', 'publish' )
			;
			
		}
		
		
		return $oQuery;
	}
	
	
}
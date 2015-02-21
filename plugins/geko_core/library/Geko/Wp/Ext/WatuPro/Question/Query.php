<?php

//
class Geko_Wp_Ext_WatuPro_Question_Query extends Geko_Wp_Entity_Query
{
	
	
	//
	public function modifyQuery( $oQuery, $aParams ) {
		
		// apply super-class manipulations
		$oQuery = parent::modifyQuery( $oQuery, $aParams );
		
		
		$oQuery
			
			->field( 'q.ID' )
			->field( 'q.question' )
			->field( 'q.answer_type' )
			->field( 'q.exam_id' )
			->field( 'q.sort_order' )
			->field( 'q.explain_answer' )
			->field( 'q.cat_id' )
			->field( 'c.name' )
			
			->from( '##pfx##watupro_question', 'q' )
			
			->joinLeft( '##pfx##watupro_qcats', 'c' )
				->on( 'c.ID = q.cat_id' )
			
		;
		
		
		// exam_id was provided
		if ( $iExamId = intval( $aParams[ 'exam_id' ] ) ) {
			
			$oQuery->where( 'q.exam_id = ?', $iExamId );
		}
		
		
		// taking_id was provided
		if ( $iTakingId = intval( $aParams[ 'taking_id' ] ) ) {
			
			$oQuery
				->joinLeft( '##pfx##watupro_taken_exams', 't' )
					->on( 'q.exam_id = t.exam_id' )
				->where( 't.ID = ?', $iTakingId )
				->order( 'q.sort_order' )
			;
		}		
		
		
		// filter by category
		if ( $iSubjectId = intval( $aParams[ 'subject' ] ) ) {
			
			$oQuery->where( 'q.cat_id = ?', $iSubjectId );
		}
		
		
		// filter by correctness
		if ( $sCorrect = trim( $aParams[ 'correct' ] ) ) {
			
			$iCorrect = ( 'correct' == $sCorrect ) ? 1 : 0 ;
			
			$oQuery
				->joinLeft( '##pfx##watupro_student_answers', 's' )
					->on( 's.taking_id = t.ID' )
					->on( 's.question_id = q.ID' )
				->where( 's.is_correct = ?', $iCorrect )					
			;
		}
		
		
		return $oQuery;
	}
	
	
}





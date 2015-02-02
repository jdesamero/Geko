<?php

//
class Geko_Wp_Ext_WatuPro_Qcats_Query extends Geko_Wp_Entity_Query
{
	
	
	//
	public function modifyQuery( $oQuery, $aParams ) {
		
		// apply super-class manipulations
		$oQuery = parent::modifyQuery( $oQuery, $aParams );
		
	
		$oQuery
			
			->field( 'c.ID' )
			->field( 'c.name' )
						
			->from( '##pfx##watupro_qcats', 'c' )
			
		;
		
		
		if (
			( $iExamId = intval( $aParams[ 'exam_id' ] ) ) || 
			( $iTakingId = intval( $aParams[ 'taking_id' ] ) )
		) {
			
			$oExamSubQuery = new Geko_Sql_Select();
			$oExamSubQuery
				->distinct( TRUE )
				->field( 'q.cat_id' )
				->from( '##pfx##watupro_question', 'q' )
			;
			
			if ( $iExamId ) {
				$oExamSubQuery->where( 'q.exam_id = ?', $iExamId );
			}
			
			if ( $iTakingId ) {
				
				$oExamSubQuery
					->joinLeft( '##pfx##watupro_taken_exams', 't' )
						->on( 't.exam_id = q.exam_id' )
					->where( 't.ID = ?', $iTakingId )
				;
				
				
				// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
				
				$oQcountSubQuery = new Geko_Sql_Select();
				$oQcountSubQuery
					
					->field( 'q1.cat_id' )
					->field( 'COUNT(*)', 'num_questions' )
					->field( 'SUM( s.is_correct )', 'num_correct_questions' )
					
					->from( '##pfx##watupro_question', 'q1' )
					
					->joinLeft( '##pfx##watupro_student_answers', 's' )
						->on( 's.question_id = q1.ID' )
						->on( 's.taking_id = ?', $iTakingId )
					
					->group( 'q1.cat_id' )
				;
				
				$oQuery
					
					->field( 'qz.num_questions' )
					->field( 'qz.num_correct_questions' )
	
					->joinLeft( $oQcountSubQuery, 'qz' )
						->on( 'qz.cat_id = c.ID' )
						
					->order( '( qz.num_correct_questions / qz.num_questions)', 'DESC' )
					
	
				;
				
			}
			
			$oQuery->where( 'c.ID IN (?)', $oExamSubQuery );
			
			
		}
		
		
		return $oQuery;
	}
	
	
	
}
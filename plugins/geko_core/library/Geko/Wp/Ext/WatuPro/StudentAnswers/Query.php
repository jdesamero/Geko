<?php

//
class Geko_Wp_Ext_WatuPro_StudentAnswers_Query extends Geko_Wp_Entity_Query
{
	
	
	//
	public function modifyQuery( $oQuery, $aParams ) {
		
		// apply super-class manipulations
		$oQuery = parent::modifyQuery( $oQuery, $aParams );
		
	
		$oQuery
			
			->field( 's.ID' )
			->field( 's.user_id' )
			->field( 's.exam_id' )
			->field( 's.taking_id' )
			->field( 's.question_id' )
			->field( 's.answer' )
			->field( 's.is_correct' )
			
			->from( '##pfx##watupro_student_answers', 's' )
			
		;
		
		// exam_id was given
		if ( $iExamId = intval( $aParams[ 'exam_id' ] ) ) {
			$oQuery->where( 's.exam_id = ?', $iExamId );
		}
		
		// taking_id was given
		if ( $iTakingId = intval( $aParams[ 'taking_id' ] ) ) {
			$oQuery->where( 's.taking_id = ?', $iTakingId );
		}
		
		// user_id was given
		if ( $iUserId = intval( $aParams[ 'user_id' ] ) ) {
			$oQuery->where( 's.user_id = ?', $iUserId );
		}
		
		
		return $oQuery;
	}
	
	
}
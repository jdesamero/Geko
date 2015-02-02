<?php

//
class Geko_Wp_Ext_WatuPro_Correct_Query extends Geko_Wp_Entity_Query
{
	
	
	//
	public function modifyQuery( $oQuery, $aParams ) {
		
		// apply super-class manipulations
		$oQuery = parent::modifyQuery( $oQuery, $aParams );
		
	
		$oQuery
			
			->field( 'c.ID' )
			->field( 'c.is_correct' )
			->field( 'COUNT( "c.is_correct" )' )
						
			->from( '##pfx##watupro_student_answers', 'c' )
	
		;
		
		if ( ( $iUserId = intval( $aParams[ 'user_id' ] ) ) &&  ( $iExamId = intval( $aParams[ 'exam_id' ] ) ) && ( $iTakingId = intval( $aParams[ 'taking_id' ] ) ) ) {
			
			$oQuery->where( sprintf( 'user_id = %d AND exam_id = %d AND taking_id = %d AND is_correct = 1', $iUserId, $iExamId, $iTakingId ) );
		}
	
		return $oQuery;
	}
	
	
}
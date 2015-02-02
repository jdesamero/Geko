<?php

//
class Geko_Wp_Ext_WatuPro_Answer_Query extends Geko_Wp_Entity_Query
{
	
	
	//
	public function modifyQuery( $oQuery, $aParams ) {
		
		// apply super-class manipulations
		$oQuery = parent::modifyQuery( $oQuery, $aParams );
		
		
		$oQuery
			
			->field( 'a.ID' )
			->field( 'a.answer' )
			->field( 'a.question_id' )
			->field( 'a.correct' )
			
			->from( '##pfx##watupro_answer', 'a' )
			
		;
		
		
		if ( $mQuestionId = $aParams[ 'question_id' ] ) {
			
			$oQuery->where( 'a.question_id * ($)', $mQuestionId );
		}
		
		return $oQuery;
	}
	
	
}




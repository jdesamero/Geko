<?php

//
class Geko_Wp_Ext_WatuPro_Filter_Query extends Geko_Wp_Entity_Query
{
	
	
	//
	public function modifyQuery( $oQuery, $aParams ) {
		
		// apply super-class manipulations
		$oQuery = parent::modifyQuery( $oQuery, $aParams );
		
	
		$oQuery
					
			->from( '##pfx##watupro_qcats', 'c' )
			
			->joinLeft( '##pfx##watupro_question', 'q' )
				->on( 'q.cat_id = c.ID' )
	
			->joinLeft( '##pfx##watupro_taken_exams', 't' )
				->on( 'q.exam_id = t.exam_id' )
			
			->joinLeft( '##pfx##watupro_student_answers', 's' )
				->on( 's.taking_id = t.ID AND s.question_id = q.ID' )
		;
		
		if ( $iTakingId = intval( $aParams[ 'taking_id' ] ) ) {
			
			if( $aParams[ 'correct' ] == 'correct' ) {
				$sScore = sprintf( 's.is_correct = 1' );
			} elseif( $aParams[ 'correct' ] == 'incorrect' ) {
				$sScore = sprintf( 's.is_correct = 0' );
			}
			
			if( !$aParams[ 'correct' ] ) {
				$oQuery->where( sprintf( 'q.cat_id = %d AND t.ID = %d', $aParams[ 'subject' ], $iTakingId ) );
			} else {
				$oQuery->where( sprintf( 'q.cat_id = %d AND t.ID = %d AND %s', $aParams[ 'subject' ], $iTakingId, $sScore ) );
			}
			
		}
	
		return $oQuery;
	}
	
	
}
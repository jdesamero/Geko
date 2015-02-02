<?php

//
class Geko_Wp_Ext_WatuPro_Purchased_Query extends Geko_Wp_Entity_Query
{
	
	
	//
	public function modifyQuery( $oQuery, $aParams ) {
		
		// apply super-class manipulations
		$oQuery = parent::modifyQuery( $oQuery, $aParams );
		
	
		$oQuery
			
			->distinct( true )
			//->field( 'p.ID' )
			->field( 'p.status' )
			->field( 'm.name' )
			//->field( 'm.ID' )
			->field( 'm.fee' )
			->field( 'm.is_scheduled' )
			->field( 'm.times_to_take')
			->field( 'DATE_FORMAT( m.schedule_to, "%M %e, %Y") ', 'schedule_to_formatted' )
			->field( 'm.schedule_to', 'schedule_to_unformatted' )
			->field( 'po.ID', 'exam_post_id' )
			//->field( 'COUNT( "t.exam_id" )', 'times_taken' )
			
			->from( '##pfx##watupro_payments', 'p' )
			
			->joinLeft( '##pfx##watupro_master', 'm' )
				->on( 'p.exam_id = m.ID' )
				
			->joinLeft( '##pfx##watupro_taken_exams', 't' )
				->on( 't.exam_id = m.ID' )
			
			->joinLeft( '##pfx##posts', 'po' )
				->on( "po.post_content LIKE CONCAT( '%[watupro ', m.ID, ']%' )" )
				->on( 'po.post_status = ?', 'publish' )
							
			
				
		;
		
		
		if ( $iUserId = intval( $aParams[ 'user_id' ] ) ) {
			
			$oQuery
				
				->where( 'p.user_id = ?', $iUserId )
				->where( "p.status = 'completed'" )
			
			;
						
		}
	
		return $oQuery;
	}
	
	
}
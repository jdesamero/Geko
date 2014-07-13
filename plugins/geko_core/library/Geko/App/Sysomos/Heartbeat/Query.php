<?php

//
class Geko_App_Sysomos_Heartbeat_Query extends Geko_App_Entity_Query
{
	
	
	//
	public function modifyQuery( $oQuery, $aParams ) {
		
		// apply super-class manipulations
		$oQuery = parent::modifyQuery( $oQuery, $aParams );
		
		
		
		$oQuery
			
			->field( 'h.hid', 'hid' )
			->field( 'h.name', 'name' )
			->field( 'h.date_created', 'date_created' )
			->field( 'h.date_modified', 'date_modified' )
			
			->from( 'heartbeat', 'h' )
			
		;
		
		
		//
		if ( $iId = intval( $aParams[ 'id' ] ) ) {
			$oQuery->where( 'h.hid = ?', $iId );
		}
		
		
		return $oQuery;
	}

}


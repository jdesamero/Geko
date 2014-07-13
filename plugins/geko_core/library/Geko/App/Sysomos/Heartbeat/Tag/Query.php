<?php

//
class Geko_App_Sysomos_Heartbeat_Tag_Query extends Geko_App_Entity_Query
{
	
	
	//
	public function modifyQuery( $oQuery, $aParams ) {
		
		// apply super-class manipulations
		$oQuery = parent::modifyQuery( $oQuery, $aParams );
		
		
		
		$oQuery
			
			->field( 't.name' )
			->field( 't.title' )
			->field( 't.mentions' )
			
			->from( 'hb_tag', 't' )
			
		;
		
		
		//
		if ( $sFilter = $aParams[ 'filter' ] ) {
			
			if ( 'team' == $sFilter ) {
				
				$oQuery->where( "t.title LIKE 'team%'" );
			
			} else if ( 'player' == $sFilter ) {

				$oQuery->where( "t.title NOT LIKE 'team%'" );
			
			}
			
		}
		
		
		//
		if ( $sTitle = trim( $aParams[ 'title' ] ) ) {
			$oQuery->where( 't.title = ?', $sTitle );
		}
		
		
		return $oQuery;
	}

}


<?php

//
class Geko_App_Sysomos_Geo_Continent_Query extends Geko_App_Entity_Query
{
	
	
	//
	public function modifyQuery( $oQuery, $aParams ) {
		
		// apply super-class manipulations
		$oQuery = parent::modifyQuery( $oQuery, $aParams );
		
		
		
		$oQuery
			
			->field( 't.id', 'id' )
			->field( 't.abbr', 'abbr' )
			->field( 't.name', 'name' )
			
			->from( 'geo_continent', 't' )
			
		;
		
		
		//
		if ( $iId = intval( $aParams[ 'id' ] ) ) {
			$oQuery->where( 't.id = ?', $iId );
		}
		
		//
		if ( $sAbbr = trim( $aParams[ 'abbr' ] ) ) {
			$oQuery->where( 't.abbr = ?', $sAbbr );
		}
		
		
		return $oQuery;
	}
	
	
}


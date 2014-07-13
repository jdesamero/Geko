<?php

//
class Geko_App_Sysomos_Geo_Country_Query extends Geko_App_Entity_Query
{
	
	
	//
	public function modifyQuery( $oQuery, $aParams ) {
		
		// apply super-class manipulations
		$oQuery = parent::modifyQuery( $oQuery, $aParams );
		
		
		
		$oQuery
			
			->field( 'c.id', 'id' )
			->field( 'c.abbr', 'abbr' )
			->field( 'c.name', 'name' )
			->field( 'c.continent_id', 'continent_id' )
			->field( 'c.latitude', 'latitude' )
			->field( 'c.longitude', 'longitude' )
			
			->from( 'geo_country', 'c' )
			
		;
		
		
		//
		if ( $iId = intval( $aParams[ 'id' ] ) ) {
			$oQuery->where( 'c.id = ?', $iId );
		}
		
		//
		if ( $sAbbr = trim( $aParams[ 'abbr' ] ) ) {
			$oQuery->where( 'c.abbr = ?', $sAbbr );
		}
		
		
		return $oQuery;
	}
	
	
}


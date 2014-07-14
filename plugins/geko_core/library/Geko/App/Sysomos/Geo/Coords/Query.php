<?php

//
class Geko_App_Sysomos_Geo_Coords_Query extends Geko_App_Entity_Query
{
	
	
	//
	public function modifyQuery( $oQuery, $aParams ) {
		
		// apply super-class manipulations
		$oQuery = parent::modifyQuery( $oQuery, $aParams );
		
		
		
		$oQuery
			
			->field( 'o.id', 'id' )
			->field( 'o.address', 'address' )
			->field( 'o.hash', 'hash' )
			->field( 'o.type', 'type' )
			->field( 'o.lat', 'lat' )
			->field( 'o.lng', 'lng' )
			->field( 'o.ne_lat', 'ne_lat' )
			->field( 'o.ne_lng', 'ne_lng' )
			->field( 'o.sw_lat', 'sw_lat' )
			->field( 'o.sw_lng', 'sw_lng' )
			->field( 'o.country_id', 'country_id' )
			
			->from( 'geo_coords', 'o' )
			
		;
		
		
		//
		if ( $iId = intval( $aParams[ 'id' ] ) ) {
			$oQuery->where( 'l.id = ?', $iId );
		}
		
		//
		if ( $sHash = trim( $aParams[ 'hash' ] ) ) {
			$oQuery->where( 'l.hash = ?', $sHash );
		}
		
		
		
		return $oQuery;
	}
	
	
}


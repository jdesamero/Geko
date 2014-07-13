<?php

//
class Geko_App_Sysomos_Geo_Location_Query extends Geko_App_Entity_Query
{
	
	
	//
	public function modifyQuery( $oQuery, $aParams ) {
		
		// apply super-class manipulations
		$oQuery = parent::modifyQuery( $oQuery, $aParams );
		
		
		
		$oQuery
			
			->field( 'l.id', 'id' )
			->field( 'l.hash', 'hash' )
			->field( 'l.location', 'location' )
			->field( 'l.latitude', 'latitude' )
			->field( 'l.longitude', 'longitude' )
			->field( 'l.country_id', 'country_id' )
			->field( 'l.match_count', 'match_count' )
			->field( 'l.ignore', 'ignore' )
			->field( 'l.status', 'status' )
			
			->from( 'geo_location', 'l' )
			
		;
		
		
		//
		if ( $iId = intval( $aParams[ 'id' ] ) ) {
			$oQuery->where( 'l.id = ?', $iId );
		}
		
		//
		if ( $sHash = trim( $aParams[ 'hash' ] ) ) {
			$oQuery->where( 'l.hash = ?', $sHash );
		}
		
		
		//// coords filter
		
		if ( $aParams[ 'no_coords' ] ) {
			$oQuery
				->where( "( l.latitude IS NULL ) OR ( l.latitude = '' )" )
				->where( "( l.longitude IS NULL ) OR ( l.longitude = '' )" )
			;
		}
		
		if ( $aParams[ 'has_coords' ] ) {
			$oQuery
				->where( "( l.latitude IS NOT NULL ) AND ( l.latitude != '' )" )
				->where( "( l.longitude IS NOT NULL ) AND ( l.longitude != '' )" )
			;
		}
		
		
		//// ignore
		
		if ( $aParams[ 'exclude_ignore' ] ) {
			$oQuery
				->where( "( l.ignore IS NULL ) OR ( l.ignore = '' ) OR ( l.ignore = 0 )" )
			;
		}
		
		
		//// status
		
		if ( $aParams[ 'exclude_has_status' ] ) {
			$oQuery
				->where( "( l.status IS NULL ) OR ( l.status = '' ) OR ( l.status = 0 )" )
			;
		}
		
		
		return $oQuery;
	}
	
	
}


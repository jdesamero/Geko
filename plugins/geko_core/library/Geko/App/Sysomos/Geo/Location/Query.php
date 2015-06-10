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
			->field( 'l.country_id', 'country_id' )
			->field( 'l.match_count', 'match_count' )
			->field( 'l.ignore', 'ignore' )
			->field( 'l.status', 'status' )
			->field( 'l.query_count', 'query_count' )
			
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
			
			$oSubQuery = new Geko_Sql_Select();
			$oSubQuery
				->field( 'r.loc_id' )
				->from( 'geo_loc_coords_rel', 'r' )
			;
			
			$oQuery->where( sprintf( 'l.id NOT IN (%s)', strval( $oSubQuery ) ) );
			
		}
		
		if ( $aParams[ 'has_coords' ] ) {
			
			$oQuery

				->field( 'o.lat', 'lat' )
				->field( 'o.lng', 'lng' )
				
				->joinInner( 'geo_loc_coords_rel', 'r' )
					->on( 'r.loc_id = l.id' )
					
				->joinInner( 'geo_coords', 'o' )
					->on( 'o.id = r.coord_id' )
				
				->where( 'r.idx = 1' )
			;
			
		}
		
		
		//// ignore
		
		if ( $aParams[ 'exclude_ignore' ] ) {
			
			$oQuery->where( "( l.ignore IS NULL ) OR ( l.ignore = '' ) OR ( l.ignore = 0 )" );
		}
		
		
		//// status
		
		if ( $aParams[ 'exclude_has_status' ] ) {
			
			$oQuery->where( "( l.status IS NULL ) OR ( l.status = '' ) OR ( l.status = 0 )" );
		}
		
		
		
		//// query count
		
		if ( $aParams[ 'no_query_count' ] ) {
			
			$oQuery->where( "( l.query_count IS NULL ) OR ( l.query_count = 0 )" );
		}
		
		
		return $oQuery;
		
	}
	
	
}


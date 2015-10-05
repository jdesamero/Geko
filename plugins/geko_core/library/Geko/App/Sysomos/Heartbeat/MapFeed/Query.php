<?php

//
class Geko_App_Sysomos_Heartbeat_MapFeed_Query extends Geko_App_Entity_Query
{
	
	
	//
	public function modifyQuery( $oQuery, $aParams ) {
		
		// apply super-class manipulations
		$oQuery = parent::modifyQuery( $oQuery, $aParams );
		
		
		
		$oQuery
			
			->field( 'f.seq' )
			->field( 'l.location' )
			->field( 'COALESCE( o.lat, c.latitude )', 'latitude' )
			->field( 'COALESCE( o.lng, c.longitude )', 'longitude' )
			
			->from( 'hb_map_feed', 'f' )
			->joinLeft( 'geo_location', 'l' )
				->on( 'l.id = f.loc_id' )
			
			->joinLeft( 'geo_loc_coords_rel', 'r' )
				->on( 'r.loc_id = l.id' )
			
			->joinLeft( 'geo_coords', 'o' )
				->on( 'o.id = r.coord_id' )			
			
			->joinLeft( 'geo_country', 'c' )
				->on( 'c.id = l.country_id' )
			
			->where( 'r.idx = 1' )
		;
		
		
		//
		if ( $iLastSeq = $aParams[ 'last_seq' ] ) {
			$oQuery->where( 'f.seq > ?', $iLastSeq );
		}
		
		
		// basic filtering
		// TO DO: MAKE THIS BETTER!!!
		$aFilterWords = array( 'unknown', 'world', 'everywhere', 'nowhere', 'football-stadiums', 'earth', 'finanshul', 'twitterhq', 'global' );
		
		foreach ( $aFilterWords as $sWord ) {
			$oQuery->where( "l.location NOT LIKE '%$%'", $sWord );
		}
		
		$oQuery
			->where( "( ( o.lat IS NOT NULL ) AND ( o.lat != '' ) ) OR ( ( c.latitude IS NOT NULL ) AND ( c.latitude != '' ) )" )
			->where( "( ( o.lng IS NOT NULL ) AND ( o.lng != '' ) ) OR ( ( c.longitude IS NOT NULL ) AND ( c.longitude != '' ) )" )
		;
		
		return $oQuery;
	}

}


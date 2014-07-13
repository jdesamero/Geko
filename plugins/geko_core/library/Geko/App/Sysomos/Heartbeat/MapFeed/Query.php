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
			->field( 'COALESCE( l.latitude, c.latitude )', 'latitude' )
			->field( 'COALESCE( l.longitude, c.longitude )', 'longitude' )
			
			->from( 'hb_map_feed', 'f' )
			->joinLeft( 'geo_location', 'l' )
				->on( 'l.id = f.loc_id' )

			->joinLeft( 'geo_country', 'c' )
				->on( 'c.id = l.country_id' )
			
		;
		
		
		//
		if ( $iLastSeq = $aParams[ 'last_seq' ] ) {
			$oQuery->where( 'f.seq > ?', $iLastSeq );
		}
		
		
		// basic filtering
		$aFilterWords = array( 'unknown', 'world', 'everywhere', 'nowhere', 'football-stadiums', 'earth', 'finanshul', 'twitterhq', 'global' );
		
		foreach ( $aFilterWords as $sWord ) {
			$oQuery->where( sprintf( "l.location NOT LIKE '%%%s%%'", $sWord ) );
		}
		
		$oQuery
			->where( "( ( l.latitude IS NOT NULL ) AND ( l.latitude != '' ) ) OR ( ( c.latitude IS NOT NULL ) AND ( c.latitude != '' ) )" )
			->where( "( ( l.longitude IS NOT NULL ) AND ( l.longitude != '' ) ) OR ( ( c.longitude IS NOT NULL ) AND ( c.longitude != '' ) )" )
		;
		
		return $oQuery;
	}

}


<?php

//
class Geko_App_Sysomos_Heartbeat_Country_Query extends Geko_App_Entity_Query
{
	
	
	//
	public function modifyQuery( $oQuery, $aParams ) {
		
		// apply super-class manipulations
		$oQuery = parent::modifyQuery( $oQuery, $aParams );
		
		
		
		$oQuery
			
			->field( 'g.abbr', 'abbr' )
			->field( 'g.name', 'name' )
			->field( 'SUM( c.mentions )', 'mention_total' )
			
			->from( 'hb_country', 'c' )
			->joinLeft( 'geo_country', 'g' )
				->on( 'g.id = c.country_id' )
			
			->group( 'c.country_id' )
			
		;
		
		
		//
		if ( $sAbbr = trim( $aParams[ 'abbr' ] ) ) {
			$oQuery->where( 'g.abbr = ?', $sAbbr );
		}
		
		
		return $oQuery;
	}

}


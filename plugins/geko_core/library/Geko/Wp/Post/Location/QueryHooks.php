<?php

//
class Geko_Wp_Post_Location_QueryHooks extends Geko_Wp_Query_Hooks_Abstract
{	
	public static $bJoin = FALSE;
	
	// helper
	protected static function getLocationField( $mParams, $sFieldName, $sTable ) {
		
		$sFields = '';
		
		if (
			( is_bool( $mParams ) && $mParams ) || 
			( is_array( $mParams ) && $mParams[ $sFieldName ] )
		) {
			$sFieldNameAs = '';
			
			if ( is_array( $mParams[ $sFieldName ] ) && $mParams[ $sFieldName ][ 'as' ] ) {
				$sFieldNameAs = $mParams[ $sFieldName ][ 'as' ];
			}
			
			$sFields = " , {$sTable}.{$sFieldName} " . ( $sFieldNameAs ? ' AS ' . $sFieldNameAs . ' ' : '' );
		}
		
		return $sFields;
		
	}
	
	//
	public static function fields( $sFields ) {
		
		if ( $mParams = self::$oWpQuery->query_vars[ 'location_fields' ] ) {
			
			// standard search fields
			
			$aFields = array(
				'street_name' => 'gklca',
				'street_number' => 'gklca',			
				'address_line_1' => 'gklca',
				'address_line_2' => 'gklca',
				'address_line_3' => 'gklca',
				'city' => 'gklca',
				'postal_code' => 'gklca',
				'latitude' => 'gklca',
				'lat_offset' => 'gklca',
				'longitude' => 'gklca',
				'long_offset' => 'gklca',
				'province_id' => 'gklcp',
				'province_name' => 'gklcp',
				'province_abbr' => 'gklcp',
				'country_id' => 'gklcc',
				'country_name' => 'gklcc',
				'country_abbr' => 'gklcc',
				'continent_id' => 'gklct',
				'continent_name' => 'gklct',
				'continent_abbr' => 'gklct'
			);
			
			foreach ( $aFields as $sFieldName => $sTable ) {
				$sFields .= self::getLocationField( $mParams, $sFieldName, $sTable );
			}
			
			// proximity search
			
			if ( $aProximity = $mParams[ 'proximity' ] ) {
				
				$iEarthRadius = ( in_array( $aProximity[ 'units' ], array( 'k', 'km', 'kilometers' ) ) ) ? 6371 : 3956;
				
				$aCoords = $aProximity[ 'coordinates' ];
				
				$fLat = floatval( Geko_String::coalesce( $aCoords[ 'lat' ], $aCoords[ 0 ] ) );
				$fLon = floatval( Geko_String::coalesce( $aCoords[ 'lon' ], $aCoords[ 1 ] ) );
				
				$sFields .= " , 
					{$iEarthRadius} * 2 * ASIN( SQRT( POWER(
						SIN( ( {$fLat} - ABS( gklca.latitude ) ) * PI() / 180 / 2), 2 ) + 
						COS( {$fLat} * PI() / 180 ) * 
						COS( abs( gklca.latitude ) * PI() / 180) * 
						POWER( SIN( ( {$fLon} - gklca.longitude ) * PI() / 180 / 2 ),
						2
					) ) ) AS distance
				";
				
			}
			
		}
		
		return $sFields;
	}
	
	//
	public static function join( $sJoin ) {
		
		global $wpdb;
		
		if ( $mParams = self::$oWpQuery->query_vars[ 'location_fields' ] ) {
			
			if (
				( is_bool( $mParams ) && $mParams ) || 
				( is_array( $mParams ) && (
					$mParams[ 'street_name' ] || $mParams[ 'street_number' ] || 
					$mParams[ 'address_line_1' ] || $mParams[ 'address_line_2' ] || $mParams[ 'address_line_3' ] || 
					$mParams[ 'city' ] || $mParams[ 'postal_code' ] || 
					$mParams[ 'latitude' ] || $mParams[ 'lat_offset' ] || 
					$mParams[ 'longitude' ] || $mParams[ 'long_offset' ] 
				) )
			) $sJoin .= "
				LEFT JOIN				{$wpdb->geko_location_address} gklca
					ON					( gklca.object_id = {$wpdb->posts}.ID  ) AND 
										( gklca.objtype_id = '" . Geko_Wp_Options_MetaKey::getId( 'post' ) . "' )
			";
			
			if (
				( is_bool( $mParams ) && $mParams ) || 
				( is_array( $mParams ) && (
					$mParams[ 'province_id' ] || $mParams[ 'province_name' ] || $mParams[ 'province_abbr' ]
				) )
			) $sJoin .= "
				LEFT JOIN				{$wpdb->geko_location_province} gklcp
					ON					( gklcp.province_id = gklca.province_id  )
			";
			
			if (
				( is_bool( $mParams ) && $mParams ) || 
				( is_array( $mParams ) && (
					$mParams[ 'country_id' ] || $mParams[ 'country_name' ] || $mParams[ 'country_abbr' ]
				) )
			) $sJoin .= "
				LEFT JOIN				{$wpdb->geko_location_country} gklcc
					ON					( gklcc.country_id = gklcp.country_id  )
			";
			
			if (
				( is_bool( $mParams ) && $mParams ) || 
				( is_array( $mParams ) && (
					$mParams[ 'continent_id' ] || $mParams[ 'continent_name' ] || $mParams[ 'continent_abbr' ]
				) )
			) $sJoin .= "
				LEFT JOIN				{$wpdb->geko_location_continent} gklct
					ON					( gklct.continent_id = gklcc.continent_id  )
			";

		}
		
		return $sJoin;
	}
	
	
	//
	public static function groupby( $sGroupBy ) {
		
		global $wpdb;
		
		if ( $mParams = self::$oWpQuery->query_vars[ 'location_fields' ] ) {
			
			if ( $aProximity = $mParams[ 'proximity' ] ) {
				$fDistance = floatval( $aProximity[ 'distance' ] );
				$sGroupBy .= ' ' . ( self::hasHaving( $sGroupBy ) ? ' AND ' : ' HAVING ' ) . " ( distance <= {$fDistance} ) ";
			}
			
		}
		
		return $sGroupBy;
	}
	
	
	//
	public static function orderby( $sOrderby ) {

		if ( 'distance' == self::$oWpQuery->query_vars['orderby'] ) {
			$sOrder = ( 'DESC' == strtoupper( self::$oWpQuery->query_vars['order'] ) ) ? 'DESC' : 'ASC';
			$sOrderby = " distance $sOrder ";
		}
		
		return $sOrderby;
	}
	
	//
	public static function register() {
		parent::register( __CLASS__ );
	}
	
	//
	public static function getJoinKey() {
		return parent::getJoinKey( __CLASS__ );
	}
	
}



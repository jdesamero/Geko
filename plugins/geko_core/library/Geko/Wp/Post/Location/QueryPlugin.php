<?php

//
class Geko_Wp_Post_Location_QueryPlugin extends Geko_Entity_Query_Plugin
{
	
	
	// helper
	protected function getLocationField( $oQuery, $mParams, $sFieldName, $sTable ) {
		
		if (
			( is_bool( $mParams ) && $mParams ) || 
			( is_array( $mParams ) && $mParams[ $sFieldName ] )
		) {
			
			$sAlias = '';
			
			if ( is_array( $mParams[ $sFieldName ] ) && $mParams[ $sFieldName ][ 'as' ] ) {
				$sAlias = $mParams[ $sFieldName ][ 'as' ];
			}
			
			$oQuery->field( sprintf( '%s.%s', $sTable, $sFieldName ), $sAlias );
		}
		
		return $oQuery;
	}
	
	
	
	//
	public function modifyQuery( $oQuery, $aParams ) {
		
		global $wpdb;
		
		$mParams = $aParams[ 'location_fields' ];
		$bOrderByDistance = ( 'distance' == $aParams[ 'orderby' ] );
		
		if ( $mParams || $bOrderByDistance ) {
			
			
			//// field mappings
			
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
			
			
			//// fields
			
			foreach ( $aFields as $sFieldName => $sTable ) {
				
				$oQuery = $this->getLocationField( $oQuery, $mParams, $sFieldName, $sTable );
			}
			
			
			//
			if ( $aProximity = $mParams[ 'proximity' ] ) {
				
				$iEarthRadius = ( in_array( $aProximity[ 'units' ], array( 'k', 'km', 'kilometers' ) ) ) ? 6371 : 3956;
				
				$aCoords = $aProximity[ 'coordinates' ];
				
				$fLat = floatval( Geko_String::coalesce( $aCoords[ 'lat' ], $aCoords[ 0 ] ) );
				$fLon = floatval( Geko_String::coalesce( $aCoords[ 'lon' ], $aCoords[ 1 ] ) );
				$fDistance = floatval( $aProximity[ 'distance' ] );
				
				$oQuery
					
					->field( sprintf( '%d * 2 * ASIN( SQRT( POWER(
						SIN( ( %f - ABS( gklca.latitude ) ) * PI() / 180 / 2 ), 2 ) + 
						COS( %f * PI() / 180 ) * 
						COS( abs( gklca.latitude ) * PI() / 180) * 
						POWER( SIN( ( %f - gklca.longitude ) * PI() / 180 / 2 ),
						2
					) ) )', $iEarthRadius, $fLat, $fLat, $fLon ), 'distance' )
					
					->having( 'distance <= ?', $fDistance )
				;
				
			}
			
			//// joins
			
			$bHasAddress = FALSE;
			$bHasState = FALSE;
			$bHasCountry = FALSE;
			$bHasContinent = FALSE;
			
			if ( is_bool( $mParams ) ) {
				$bHasAddress = TRUE;
				$bHasState = TRUE;
				$bHasCountry = TRUE;
				$bHasContinent = TRUE;
			}
			
			
			if ( is_array( $mParams ) ) {
				
				if (
					$mParams[ 'street_name' ] || $mParams[ 'street_number' ] || 
					$mParams[ 'address_line_1' ] || $mParams[ 'address_line_2' ] || $mParams[ 'address_line_3' ] || 
					$mParams[ 'city' ] || $mParams[ 'postal_code' ] || 
					$mParams[ 'latitude' ] || $mParams[ 'lat_offset' ] || 
					$mParams[ 'longitude' ] || $mParams[ 'long_offset' ] 
				) {
					$bHasAddress = TRUE;
				}
				
				if ( $mParams[ 'province_id' ] || $mParams[ 'province_name' ] || $mParams[ 'province_abbr' ] ) {
					$bHasAddress = TRUE;
					$bHasState = TRUE;
				}
				
				if ( $mParams[ 'country_id' ] || $mParams[ 'country_name' ] || $mParams[ 'country_abbr' ] ) {
					$bHasAddress = TRUE;
					$bHasState = TRUE;
					$bHasCountry = TRUE;
				}
				
				if ( $mParams[ 'continent_id' ] || $mParams[ 'continent_name' ] || $mParams[ 'continent_abbr' ] ) {
					$bHasAddress = TRUE;
					$bHasState = TRUE;
					$bHasCountry = TRUE;
					$bHasContinent = TRUE;
				}
			}
			
			
			if ( $bHasAddress ) {
				$oQuery
					->joinLeft( $wpdb->geko_location_address, 'gklca' )
						->on( 'gklca.object_id = p.ID' )
						->on( 'gklca.objtype_id = ?', Geko_Wp_Options_MetaKey::getId( 'post' ) )
				;
			}
			
			if ( $bHasState ) {
				$oQuery
					->joinLeft( $wpdb->geko_location_province, 'gklcp' )
						->on( 'gklcp.province_id = gklca.province_id' )
				;
			}
						
			if ( $bHasCountry ) {
				$oQuery
					->joinLeft( $wpdb->geko_location_country, 'gklcc' )
						->on( 'gklcc.country_id = gklcp.country_id' )
				;
			}
						
			if ( $bHasContinent ) {
				$oQuery
					->joinLeft( $wpdb->geko_location_continent, 'gklct' )
						->on( 'gklct.continent_id = gklcc.continent_id' )
				;
			}
			
			
			//// order by
			
			if ( $bOrderByDistance ) {

				if ( !$sOrder = $aParams[ 'order' ] ) {
					$sOrder = 'ASC';
				}
				
				$oQuery->order( 'distance', $sOrder );
			}
			
		}
		
		
		return $oQuery;
	
	}
	
	
}




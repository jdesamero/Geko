<?php

//
class Geko_Google_Map_Query_V2 extends Geko_Google_Map_Query
{
	
	protected $_sRequestUrl = 'http://maps.google.com/maps/api/geocode/json';
	
	
	
	//
	public function formatGetParams( $sQuery ) {
		return array(
			'sensor' => 'false',
			'address' => $sQuery
		);
	}
	
	
	// implement hook method
	public function normalizeResult( $aRes ) {
		
		$aResFmt = array(
			'raw_result' => $aRes,
			'status' => strtolower( $aRes[ 'status' ] )
		);
		
		$aResults = $aRes[ 'results' ];
		
		if (
			( is_array( $aResults ) ) && 
			( $aCoords = $aResults[ 0 ][ 'geometry' ][ 'location' ] )
		) {
			
			$aResFmt = array_merge( $aResFmt, array(
				'lat' => $aCoords[ 'lat' ],
				'lng' => $aCoords[ 'lng' ],
				'zoom' => NULL,
				'matches' => count( $aRes[ 'results' ] )
			) );
			
			//
			$aDetails = array();
			
			foreach ( $aResults as $aLocInfo ) {
				
				$aGeo = $aLocInfo[ 'geometry' ];
				
				$aCenCoords = $aGeo[ 'location' ];
				$aNeCoords = $aGeo[ 'bounds' ][ 'northeast' ];
				$aSwCoords = $aGeo[ 'bounds' ][ 'southwest' ];
				
				if ( !$aNeCoords ) {
					$aNeCoords = $aGeo[ 'viewport' ][ 'northeast' ];
				}

				if ( !$aSwCoords ) {
					$aSwCoords = $aGeo[ 'viewport' ][ 'northeast' ];
				}
				
				$aTypes = array( strtolower( $aGeo[ 'location_type' ] ) );
				
				$aDetRow = array(
					'address' => $aLocInfo[ 'formatted_address' ],
					'place_id' => $aLocInfo[ 'place_id' ],
					'lat' => $aCenCoords[ 'lat' ],
					'lng' => $aCenCoords[ 'lng' ],
					'ne_lat' => $aNeCoords[ 'lat' ],
					'ne_lng' => $aNeCoords[ 'lng' ],
					'sw_lat' => $aSwCoords[ 'lat' ],
					'sw_lng' => $aSwCoords[ 'lng' ]
				);
				
				// find country
				$aComps = $aLocInfo[ 'address_components' ];
				
				if ( is_array( $aComps ) ) {
					foreach ( $aComps as $aComp ) {
						$aCompType = $aComp[ 'types' ];
						if ( is_array( $aCompType ) && in_array( 'country', $aCompType ) ) {
							$aDetRow[ 'country' ] = $aComp[ 'short_name' ];
						}
					}
				}
				
				$aCompTypes = $aLocInfo[ 'types' ];
				if ( is_array( $aCompTypes ) ) {
					$aTypes = array_merge( $aTypes, $aCompTypes );
				}
				
				$aDetRow[ 'type' ] = implode( ', ', $aTypes );
				
				// pass address components back
				$aDetRow[ 'address_components' ] = $aComps;
				
				$aDetails[] = $aDetRow;
			}
			
			$aResFmt[ 'details' ] = $aDetails;
		}
		
		return $aResFmt;
	}

	
}


/*

// version 2 result
Array
(
    [results] => Array
        (
            [0] => Array
                (
                    [address_components] => Array
                        (
                            [0] => Array
                                (
                                    [long_name] => 100
                                    [short_name] => 100
                                    [types] => Array
                                        (
                                            [0] => street_number
                                        )

                                )

                            [1] => Array
                                (
                                    [long_name] => Western Battery Road
                                    [short_name] => Western Battery Rd
                                    [types] => Array
                                        (
                                            [0] => route
                                        )

                                )

                            [2] => Array
                                (
                                    [long_name] => Liberty Village
                                    [short_name] => Liberty Village
                                    [types] => Array
                                        (
                                            [0] => neighborhood
                                            [1] => political
                                        )

                                )

                            [3] => Array
                                (
                                    [long_name] => Old Toronto
                                    [short_name] => Old Toronto
                                    [types] => Array
                                        (
                                            [0] => sublocality
                                            [1] => political
                                        )

                                )

                            [4] => Array
                                (
                                    [long_name] => Toronto
                                    [short_name] => Toronto
                                    [types] => Array
                                        (
                                            [0] => locality
                                            [1] => political
                                        )

                                )

                            [5] => Array
                                (
                                    [long_name] => Toronto Division
                                    [short_name] => Toronto Division
                                    [types] => Array
                                        (
                                            [0] => administrative_area_level_2
                                            [1] => political
                                        )

                                )

                            [6] => Array
                                (
                                    [long_name] => Ontario
                                    [short_name] => ON
                                    [types] => Array
                                        (
                                            [0] => administrative_area_level_1
                                            [1] => political
                                        )

                                )

                            [7] => Array
                                (
                                    [long_name] => Canada
                                    [short_name] => CA
                                    [types] => Array
                                        (
                                            [0] => country
                                            [1] => political
                                        )

                                )

                            [8] => Array
                                (
                                    [long_name] => M6K 3S2
                                    [short_name] => M6K 3S2
                                    [types] => Array
                                        (
                                            [0] => postal_code
                                        )

                                )

                        )

                    [formatted_address] => 100 Western Battery Road, Toronto, ON M6K 3S2, Canada
                    [geometry] => Array
                        (
                            [bounds] => Array
                                (
                                    [northeast] => Array
                                        (
                                            [lat] => 43.6397413
                                            [lng] => -79.4161653
                                        )

                                    [southwest] => Array
                                        (
                                            [lat] => 43.6397379
                                            [lng] => -79.4161831
                                        )

                                )

                            [location] => Array
                                (
                                    [lat] => 43.6397413
                                    [lng] => -79.4161653
                                )

                            [location_type] => RANGE_INTERPOLATED
                            [viewport] => Array
                                (
                                    [northeast] => Array
                                        (
                                            [lat] => 43.641088580291
                                            [lng] => -79.414825219709
                                        )

                                    [southwest] => Array
                                        (
                                            [lat] => 43.638390619709
                                            [lng] => -79.417523180292
                                        )

                                )

                        )

                    [types] => Array
                        (
                            [0] => street_address
                        )

                )

        )

    [status] => OK
)


"OK" indicates that no errors occurred; the address was successfully parsed and at least one geocode was returned.
"ZERO_RESULTS" indicates that the geocode was successful but returned no results. This may occur if the geocoder was passed a non-existent address.
"OVER_QUERY_LIMIT" indicates that you are over your quota.
"REQUEST_DENIED" indicates that your request was denied.
"INVALID_REQUEST" generally indicates that the query (address, components or latlng) is missing.
"UNKNOWN_ERROR" indicates that the request could not be processed due to a server error. The request may succeed if you try again.


*/



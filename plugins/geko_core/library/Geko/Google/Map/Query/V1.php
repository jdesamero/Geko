<?php

//
class Geko_Google_Map_Query_V1 extends Geko_Google_Map_Query
{
	
	protected $_sApiKey = NULL;
	protected $_sRequestUrl = 'http://maps.google.com/maps/geo';
	
	//
	public function __construct( $aParams = array() ) {
		
		if (
			( NULL === $this->_sApiKey ) && 
			( $sApiKey = $aParams[ 'api_key' ] )
		) {
			$this->_sApiKey = $sApiKey;
		}

		if (
			( NULL === $this->_sApiKey ) && 
			( defined( 'GOOGLEMAPS_API_KEY' ) )
		) {
			$this->_sApiKey = GOOGLEMAPS_API_KEY;
		}
		
		parent::__construct( $aParams );
	}
	
	
	// implement hook method
	public function formatGetParams( $sQuery ) {
		
		$aRet = array(
			'sensor' => 'false',
			'output' => 'json',
			'q' => $sQuery
		);
		
		if ( $this->_sApiKey ) {
			$aRet[ 'key' ] = $this->_sApiKey;
		}
		
		return $aRet;
	}
	
	
	// implement hook method
	public function normalizeResult( $aRes ) {
		
		$aResFmt = array(
			'raw_result' => $aRes
		);
		
		if ( $aCoords = $aRes[ 'Placemark' ][ 0 ][ 'Point' ][ 'coordinates' ] ) {
			$aResFmt = array_merge( $aResFmt, array(
				'lat' => $aCoords[ 1 ],
				'lng' => $aCoords[ 0 ],
				'zoom' => $aCoords[ 2 ]
			) );
		}
		
		return $aResFmt;
	}
	
	
	
}


/*

// version 1 result
Array
(
    [name] => Canada
    [Status] => Array
        (
            [code] => 200
            [request] => geocode
        )

    [Placemark] => Array
        (
            [0] => Array
                (
                    [id] => p1
                    [address] => Canada
                    [AddressDetails] => Array
                        (
                            [Accuracy] => 1
                            [Country] => Array
                                (
                                    [CountryName] => Canada
                                    [CountryNameCode] => CA
                                )

                        )

                    [ExtendedData] => Array
                        (
                            [LatLonBox] => Array
                                (
                                    [north] => 74.3830076
                                    [south] => 21.8769939
                                    [east] => -40.7803632
                                    [west] => -171.9131788
                                )

                        )

                    [Point] => Array
                        (
                            [coordinates] => Array
                                (
                                    [0] => -106.346771			// longitude
                                    [1] => 56.130366			// latitude
                                    [2] => 0					// zoom level
                                )

                        )

                )

        )

)

*/


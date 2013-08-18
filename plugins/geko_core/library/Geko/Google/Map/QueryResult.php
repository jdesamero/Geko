<?php

//
class Geko_Google_Map_QueryResult
{
	
	protected $_aQueryResult = array();
	
	// 
	public function __construct( $aQueryResult ) {
		
		if ( is_array( $aQueryResult ) ) $this->_aQueryResult = $aQueryResult;
		
	}
	
	//
	public function getRawResult() {
		return $this->_aQueryResult;
	}
	
	//
	public function getCoordinates() {
		
		if ( $aCoords = $this->_aQueryResult[ 'Placemark' ][ 0 ][ 'Point' ][ 'coordinates' ] ) {
			// let's switch so that it's the expected lat/lon order
			return array( $aCoords[ 1 ], $aCoords[ 0 ] );
		}
		
		return NULL;
	}
	
	//
	public function getZoomLevel() {
		
		if ( $aCoords = $this->_aQueryResult[ 'Placemark' ][ 0 ][ 'Point' ][ 'coordinates' ] ) {
			return $aCoords[ 2 ];
		}
		
		return NULL;
	}
	
}


/*

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


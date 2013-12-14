<?php

//
class Geko_Google_Map_Result
{
	
	protected $_aResult = array();
	
	// 
	public function __construct( $aResult ) {
		
		if ( is_array( $aResult ) ) $this->_aResult = $aResult;
		
	}
	
	//
	public function getRawResult() {
		return $this->_aResult;
	}
	
	//
	public function getCoordinates() {
		$aResult = $this->_aResult;
		return array( $aResult[ 'lat' ], $aResult[ 'lng' ] );
	}
	
	//
	public function getZoomLevel() {
		$aResult = $this->_aResult;
		return $aResult[ 'zoom' ];
	}
	
	
}





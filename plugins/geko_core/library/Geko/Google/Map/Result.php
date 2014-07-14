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
		return $this->_aResult[ 'zoom' ];
	}
	
	
	//
	public function getStatus() {
		return $this->_aResult[ 'status' ];	
	}
	
	//
	public function getMatches() {
		return $this->_aResult[ 'matches' ];	
	}
	
	//
	public function getDetails() {
		return $this->_aResult[ 'details' ];
	}
	
	
	//
	public function getStatusId() {
		
		$sStatus = $this->getStatus();
		
		$aStatIds = array(
			'ok' => 1,
			'zero_results' => 2,
			'over_query_limit' => 3,
			'request_denied' => 4,
			'invalid_request' => 5,
			'unknown_error' => 6
		);
		
		if ( !$iStatId = $aStatIds[ $sStatus ] ) {
			$iStatId = 999;
		}
		
		return $iStatId;
	}
	
	
}



<?php

// class that handles cached lookups

//
class Geko_CachedLookup
{
	
	// and $_oOrig object must implement a getResult() method
	protected $_oOrig = NULL;
	
	
	
	//
	public function __construct( $oOrig ) {
		
		$this->_oOrig = $oOrig;
		
	}
	
	
	
	
	//
	public function getResult() {
		
		$aArgs = func_get_args();
		
		$mNormalized = call_user_func_array( array( $this, 'getNormalized' ), $aArgs );
		
		$mHash = $this->getHash( $mNormalized );
		
		$mRes = $this->getCached( $mHash, $mNormalized, $aArgs );
		if ( !$mRes ) {
			
			$oOrig = $this->_oOrig;
			
			// do actual data retrieval
			$mOrigRes = call_user_func_array( array( $oOrig, 'getResult' ), $aArgs );
			
			$mRes = $this->getFormattedOrigRes( $mOrigRes );
			
			$this->saveToCache( $mHash, $mNormalized, $aArgs, $mRes );
		}
		
		
		return $mRes;
	}
	
	
	
	//// hook methods
		
	//
	public function getNormalized() {
		
		$aArgs = func_get_args();
		
		return $aArgs;
	}
	
	//
	public function getHash( $mNormalized ) {
		
		return $mNormalized;
	}
		
	//
	public function getFormattedOrigRes( $mOrigRes ) {
		
		return $mOrigRes;
	}
	
	
	//// cache business end
	
	//
	public function getCached( $mHash, $mNormalized, $mResArgs ) {
		
		return NULL;
	}
	
	
	//
	public function saveToCache( $mHash, $mNormalized, $mResArgs, $mRes ) {
		
		return $this;
	}
	
	
}




// Geko_IpGeolocation
// public function getResult( $sIpAddress ) {

// Geko_Sysomos_Heartbeat
// public function getResult( $sPage, $aParams = array() ) {

// Geko_Google_Map_Query
// public function getResult( $sQuery ) {




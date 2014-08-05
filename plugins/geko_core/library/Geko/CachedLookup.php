<?php

// class that handles cached lookups

//
class Geko_CachedLookup
{
	
	// and $_oOrig object must implement a getResult() method
	protected $_oOrig = NULL;
	protected $_oEngine = NULL;
	
	protected $_sInstanceClass = '';

	protected $_sDefaultOrigClass = '';
	
	protected $_sDefaultEngineSuffix = '';
	protected $_sDefaultEngineClass = '';
	
	
	
	// $oOrig = NULL, $oEngine = NULL, $aEngParams = NULL
	
	// $mOrig and $mOrig can be objects, if array then use as parameters
	public function __construct( $mOrig = NULL, $mEngine = NULL ) {
		
		$this->_sInstanceClass = get_class( $this );
		
		
		//// orig
		
		$oOrig = NULL;
		$aOrigParams = NULL;
		
		if ( is_object( $mOrig ) ) {
			$oOrig = $mOrig;
		} elseif ( is_array( $mOrig ) ) {
			$aOrigParams = $mOrig;
		}
		
		if ( NULL === $oOrig ) {
			
			$sDefOrigClass = $this->_sDefaultOrigClass;
			
			if ( !$sDefOrigClass ) {
				
				$sDefOrigClass = str_replace( '_CachedLookup', '', $this->_sInstanceClass );
				
				if ( @class_exists( $sDefOrigClass ) ) {
					$oOrig = Geko_Class::createInstance( $sDefOrigClass, $aOrigParams );
					$this->_sDefaultOrigClass = $sDefOrigClass;
				}
			}
			
		}
		
		$this->setOrig( $oOrig );
		
		
		//// engine

		$oEngine = NULL;
		$aEngParams = NULL;
		
		if ( is_object( $mEngine ) ) {
			$oEngine = $mEngine;
		} elseif ( is_array( $mEngine ) ) {
			$aEngParams = $mEngine;
		}
		
		if ( NULL === $oEngine ) {
			
			$sDefEngClass = $this->_sDefaultEngineClass;
			$sDefEngSfx = $this->_sDefaultEngineSuffix;
			
			
			// attempt to find default engine class
			if ( !$sDefEngClass ) {
				if ( $sDefEngSfx ) {
					
					$sDefEngClass = sprintf( '%s_Engine_%s', $this->_sInstanceClass, $sDefEngSfx );
					
					if ( !@class_exists( $sDefEngClass ) ) {
						$sDefEngClass = '';
					}
				}
			}
			
			if ( $sDefEngClass ) {
				$this->_sDefaultEngineClass = $sDefEngClass;
				$oEngine = Geko_Class::createInstance( $sDefEngClass, $aEngParams );
			}
			
		}
		
		
		$this->setEngine( $oEngine );
		
	}


	
	//
	public function setOrig( $oOrig ) {
	
		$this->_oOrig = $oOrig;
		
		return $this;
	}
	
	//
	public function setEngine( $oEngine ) {
	
		$this->_oEngine = $oEngine;
		
		return $this;
	}
	
	
	
	//
	public function getResult() {
		
		$aArgs = func_get_args();
		
		$aArgs = $this->getNormalized( $aArgs );
		
		$mHash = call_user_func_array( array( $this, 'getHash' ), $aArgs );
		
		$mRes = $this->getCached( $mHash, $aArgs );
		
		if ( !$mRes ) {
			
			$oOrig = $this->_oOrig;
			
			// do actual data retrieval
			$mActRes = call_user_func_array( array( $oOrig, 'getResult' ), $aArgs );
			
			// print_r( $mActRes );
			
			// save to cache
			$this->saveToCache( $mHash, $aArgs, $mActRes );
			
			// always get the cached version
			$mRes = $this->getCached( $mHash, $aArgs );
		}
		
		
		return $mRes;
	}
	
	
	
	//// hook methods
		
	//
	public function getNormalized( $aArgs ) {
		
		return $aArgs;
	}
	
	//
	public function getHash() {
		
		return NULL;
	}
	
	
	
	//// cache business end
	
	//
	public function getCached( $mHash, $aArgs ) {
		
		if ( $oEngine = $this->_oEngine ) {
			return $oEngine->getCached( $mHash, $aArgs );
		}
		
		return NULL;
	}
	
	
	//
	public function saveToCache( $mHash, $aArgs, $mActRes ) {
		
		if ( $oEngine = $this->_oEngine ) {
			$oEngine->saveToCache( $mHash, $aArgs, $mActRes );
		}
		
		return $this;
	}
	
	
}



// Geko_IpGeolocation
// public function getResult( $sIpAddress ) {

// Geko_Sysomos_Heartbeat
// public function getResult( $sPage, $aParams = array() ) {

// Geko_Google_Map_Query
// public function getResult( $sQuery ) {




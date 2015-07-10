<?php

//
class Geko_Currency_Rate_CachedLookup extends Geko_CachedLookup
{
	
	protected $_sDefaultEngineSuffix = 'Db';
	
	protected $_iCacheResetDuration = 43200;				// when to reset cache, in seconds
															// 60 * 60 * 12, twelve hours
	
	
	
	// implement custom getResult()
	public function getResult() {
		
		$aArgs = func_get_args();
		
		$oOrig = $this->_oOrig;
		
		
		// do this once
		if ( !$oOrig->hasRates() ) {
			
			// see if a cached value can be retrieved
			$aRates = $this->getCached( NULL, $aArgs );
			
			if ( $aRates ) {
				
				// use cached values
				$oOrig->setRates( $aRates );
				
				$iTimestamp = $aRates[ 'timestamp' ];
				
				if ( ( time() - $iTimestamp ) > $this->_iCacheResetDuration ) {
					
					// refresh the cache with live values
					$this->saveRatesToCache( $aArgs, $aRates );				
				}
				
			} else {
				
				// get live values
				$this->saveRatesToCache( $aArgs, $aRates );			
				
			}
			
		}
		
		
		// do actual data retrieval
		return call_user_func_array( array( $oOrig, 'getResult' ), $aArgs );
	}
	
	
	//
	public function saveRatesToCache( $aArgs, $aRates ) {
		
		$oOrig = $this->_oOrig;
		
		$aRates = $oOrig->getRates();
		$this->saveToCache( NULL, $aArgs, $aRates );			
		
		return NULL;
	}
	
	
	
}




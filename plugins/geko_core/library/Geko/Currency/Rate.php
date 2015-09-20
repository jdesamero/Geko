<?php

//
class Geko_Currency_Rate extends Geko_Http
{
	
	protected $_sApiKey = NULL;
	protected $_sRequestUrl = 'http://openexchangerates.org/latest.json';
	
	protected $_aRates = NULL;
	
	
	
	//
	public function __construct( $aParams = array() ) {
		
		if ( $sApiKey = $aParams[ 'api_key' ] ) {
			$this->_sApiKey = $sApiKey;
		}
		
	}
	
	
	
	//// functional stuff
	
	//
	public function initRates() {
		
		if ( NULL === $this->_aRates ) {
			
			// get and store the full rate table
			
			
			// for testing
			/* /
			$sMockJson = sprintf( '%s/Rate/currency.latest.json', dirname( __FILE__ ) );
			$this->_aRates = Geko_Json::decode( file_get_contents( $sMockJson ) );
			/* */
			
			
			// live
			/* */			
			$this->_aRates = $this
				->_setClientUrl( NULL, array(
					'app_id' => $this->_sApiKey
				) )
				->_getParsedResponseBody()
			;
			/* */
			
		}
		
	}
	
	//
	public function getRates() {
		
		$this->initRates();
		
		return $this->_aRates;
	}
	
	//
	public function setRates( $aRates ) {
		
		$this->_aRates = $aRates;
		
		return $this;
	}
	
	//
	public function hasRates() {
		
		return ( $this->_aRates ) ? TRUE : FALSE ;
	}
	
	
	
	
	/* return calculated rates, eg:
	 *  	CAD 			-> how much CAD is 1 USD worth?
	 *  	EUR				-> how much EUR is 1 USD worth?
	 *  	GBP				-> how much GBP is 1 USD worth?
	 *  	CAD, EUR		-> how much CAD is 1 EUR worth?
	 *  	CAD, GBP		-> how much CAD is 1 GBP worth?
	 *  	USD, CAD		-> how much USD is 1 CAD worth?
	 *  	EUR, CAD		-> how much EUR is 1 CAD worth?
	 *  	GBP, CAD		-> how much GBP is 1 CAD worth?
	 */
	public function getResult( $sTargetCountryCode, $sBaseCountryCode = NULL ) {
		
		$this->initRates();
		
		$sTrueBaseCountryCode = $this->_aRates[ 'base' ];							// this should be USD
		if ( !$sBaseCountryCode ) $sBaseCountryCode = $sTrueBaseCountryCode;		// use the "true" base
		
		if ( $fRate = $this->_aRates[ 'rates' ][ $sTargetCountryCode ] ) {
		
			if ( $sTrueBaseCountryCode != $sBaseCountryCode ) {
				
				// the base rate asked for is not the same as the "true" base
				
				// re-calculate base rate
				$fBaseRate = $this->_aRates[ 'rates' ][ $sBaseCountryCode ];
				
				if ( $fBaseRate ) {
					$fRate = $fBaseRate * ( 1 / $fRate );
				}	
			}
			
		}
		
		return $fRate;
	}
	
	
		
}



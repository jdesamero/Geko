<?php

//
class Geko_Geography_CountryState extends Geko_Singleton_Abstract
{
	
	protected $_aCountries = NULL;
	
	protected $_aStates = array();
	protected $_aStateCountryHash = array();
	
	
	
	
	//// methods
	
	//
	private function myInit() {
		
		if ( 0 == count( $this->_aStates ) ) {
			
			$this->get();		// init $this->_aCountries
			
			foreach ( $this->_aCountries as $sCountryCode => $aCountry ) {
				
				$this->_aStates = array_merge( $this->_aStates, $aCountry[ 'states' ] );
				
				foreach ( $aCountry[ 'states' ] as $sStateCode => $sStateName ) {
					$this->_aStateCountryHash[ $sStateCode ] = $sCountryCode;
					$this->_aStateCountryHash[ strtoupper( $sStateName ) ] = $sCountryCode;
				}
			}
			
		}
	}
	
	
	//
	public function get() {
		
		if ( NULL === $this->_aCountries ) {
			$oGeo = Geko_Geography_Xml::getInstance();
			$oGeo->loadData( GEKO_GEOGRAPHY_XML );
		}
		
		return $this->_aCountries;
	}
	
	//
	public function set( $aCountries ) {
		$this->_aCountries = $aCountries;
	}
	
	
	
	//
	public function getStates() {
		
		$this->myInit();
		
		return $this->_aStates;
	}
	
	//
	public function getStateNameFromStateCode( $sStateCode ) {
		
		$this->myInit();
		
		$sStateCodeNormalize = strtoupper( trim( $sStateCode ) );		// normalize
		$sRet = $this->_aStates[ $sStateCodeNormalize ];
		
		return ( '' == $sRet ) ? $sStateCode : $sRet ;
	}
	
	// alias
	public function getNameFromCode( $sCode ) {
		return $this->getStateNameFromStateCode( $sCode );
	}
	
	// $sState could be code or name
	public function getCountryCodeFromState( $sState ) {
		
		$this->myInit();
		
		$sState = strtoupper( trim( $sState ) );						// normalize
		return $this->_aStateCountryHash[ $sState ];
	}
	
	// $sState could be code or name
	public function getCountryNameFromState( $sState ) {
		
		$this->myInit();
		
		return $this->_aCountries[ $this->getCountryCodeFromState( $sState ) ][ 'name' ];
	}
	
	
}



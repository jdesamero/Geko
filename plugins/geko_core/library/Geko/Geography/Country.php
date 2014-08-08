<?php

//
class Geko_Geography_Country extends Geko_Singleton_Abstract
{	
	
	protected $_aContinents = NULL;
	protected $_a3LetterCode = NULL;
	protected $_aCountryCodeHash = NULL;
	
	protected $_aCountries = array();
	protected $_aCountryContinentHash = array();
	
	
	protected $_aCountryNameVariations = array(
		1 => array(
			'VN' => 'Vietnam'
		)
	);
	
	
	
	//
	private function myInit() {
		
		if ( 0 == count( $this->_aCountries ) ) {
			
			$this->get();		// init $this->_aContinents
			
			$aNorm = array();
			
			foreach ( $this->_aCountryCodeHash as $sKey => $sValue ) {
				$aNorm[ strtoupper( $sKey ) ] = $sValue;
			}
			
			$this->_aCountryCodeHash = $aNorm;
			
			foreach ( $this->_aContinents as $sContinentCode => $aContinent ) {
				
				$this->_aCountries = array_merge( $this->_aCountries, $aContinent[ 'countries' ] );
				
				foreach ( $aContinent[ 'countries' ] as $sCountryCode => $sCountryName ) {
					
					$sCountryNameNorm = strtoupper( $sCountryName );
					
					$this->_aCountryContinentHash[ $sCountryCode ] = $sContinentCode;
					$this->_aCountryContinentHash[ $sCountryNameNorm ] = $sContinentCode;
					$this->_aCountryCodeHash[ $sCountryNameNorm ] = $sCountryCode;
					
				}
			}
		}
	}

	
	//
	public function get() {
		
		if ( NULL === $this->_aContinents ) {
			$oGeo = Geko_Geography_Xml::getInstance();
			$oGeo->loadData( GEKO_GEOGRAPHY_XML );
		}
		
		return $this->_aContinents;
	}
	
	//
	public function set( $aContinents, $a3LetterCode, $aCountryCodeHash ) {
		$this->_aContinents = $aContinents;
		$this->_a3LetterCode = $a3LetterCode;
		$this->_aCountryCodeHash = $aCountryCodeHash;
	}
	
	
	
	//
	public function getCountries() {
		
		$this->myInit();
		
		return $this->_aCountries;
	}
	
	//
	public function getThreeLetterCodes() {
		
		$this->get();
		
		return $this->_a3LetterCode;
	}
	
	//
	public function getCountryCodeHash() {

		$this->get();
		
		return $this->_aCountryCodeHash;	
	}
	
	
	//
	public function getCountryNameFromCountryCode( $sCountryCode, $iVariation = 0 ) {
		
		$this->myInit();
		
		if ( $iVariation ) {
			$sRet = $this->_aCountryNameVariations[ $iVariation ][ $sCountryCode ];
		}
		
		if ( !$sRet ) {
			$sCountryCodeNormalize = strtoupper( trim( $sCountryCode ) );		// normalize
			$sRet = $this->_aCountries[ $sCountryCodeNormalize ];
		}
		
		return ( '' == $sRet ) ? $sCountryCode : $sRet;
	}
	
	
	//
	public function getCountryCodeFromCountryName( $sCountryName ) {
		
		$this->myInit();
		
		$sCountryNameNormalize = strtoupper( trim( $sCountryName ) );		// normalize
		$sRet = $this->_aCountryCodeHash[ $sCountryNameNormalize ];
		
		return ( '' == $sRet ) ? $sCountryName : $sRet;
	}
	
	
	// alias
	public function getNameFromCode( $sCode ) {
		return $this->getCountryNameFromCountryCode( $sCode );
	}
	
	// $sCountry could be code or name
	public function getContinentCodeFromCountry( $sCountry ) {
		
		$this->myInit();
		
		$sCountry = strtoupper( trim( $sCountry ) );						// normalize
		
		return $this->_aCountryContinentHash[ $sCountry ];
	}

	// $sState could be code or name
	public function getContinentNameFromCountry( $sCountry ) {
		
		$this->myInit();
		
		return $this->_aContinents[ $this->getContinentCodeFromCountry( $sCountry ) ][ 'name' ];
	}
	
	
	//
	public function getCountryCodeFrom3Letter( $s3Letter ) {
		
		$this->get();		// init $this->_aContinents
		
		return $this->_a3LetterCode[ strtoupper( $s3Letter ) ];
	}
	
	
	
}



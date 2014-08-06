<?php

//
class Geko_Geography_Country
{	
	
	public static $aContinents = NULL;
	public static $a3LetterCode = NULL;
	public static $aCountryCodeHash = NULL;
	
	
	//
	public static $aCountries = array();
	public static $aCountryContinentHash = array();
	
	
	
	
	public static $aCountryNameVariations = array(
		1 => array(
			'VN' => 'Vietnam'
		)
	);
	
	
	
	//
	private static function init() {
		
		if ( 0 == count( self::$aCountries ) ) {
			
			if ( NULL === self::$aCountryCodeHash ) {
				Geko_Geography_Xml::loadData();
			}
			
			$aNorm = array();
			
			foreach ( self::$aCountryCodeHash as $sKey => $sValue ) {
				$aNorm[ strtoupper( $sKey ) ] = $sValue;
			}
			
			self::$aCountryCodeHash = $aNorm;
			
			foreach ( self::$aContinents as $sContinentCode => $aContinent ) {
				
				self::$aCountries = array_merge( self::$aCountries, $aContinent[ 'countries' ] );
				
				foreach ( $aContinent[ 'countries' ] as $sCountryCode => $sCountryName ) {
					
					$sCountryNameNorm = strtoupper( $sCountryName );
					
					self::$aCountryContinentHash[ $sCountryCode ] = $sContinentCode;
					self::$aCountryContinentHash[ $sCountryNameNorm ] = $sContinentCode;
					self::$aCountryCodeHash[ $sCountryNameNorm ] = $sCountryCode;
					
				}
			}
		}
	}

	
	//
	public static function get() {
		
		if ( NULL === self::$aContinents ) {
			Geko_Geography_Xml::loadData();
		}
		
		return self::$aContinents;
	}
	
	//
	public static function set( $aContinents, $a3LetterCode, $aCountryCodeHash ) {
		self::$aContinents = $aContinents;
		self::$a3LetterCode = $a3LetterCode;
		self::$aCountryCodeHash = $aCountryCodeHash;
	}
	
	
	
	//
	public static function getCountries() {
		
		self::init();
		
		return self::$aCountries;
	}
	
	//
	public static function getCountryNameFromCountryCode( $sCountryCode, $iVariation = 0 ) {
		
		self::init();
		
		if ( $iVariation ) {
			$sRet = self::$aCountryNameVariations[ $iVariation ][ $sCountryCode ];
		}
		
		if ( !$sRet ) {
			$sCountryCodeNormalize = strtoupper( trim( $sCountryCode ) );		// normalize
			$sRet = self::$aCountries[ $sCountryCodeNormalize ];
		}
		
		return ( '' == $sRet ) ? $sCountryCode : $sRet;
	}
	
	
	//
	public static function getCountryCodeFromCountryName( $sCountryName ) {
		
		self::init();
		
		$sCountryNameNormalize = strtoupper( trim( $sCountryName ) );		// normalize
		$sRet = self::$aCountryCodeHash[ $sCountryNameNormalize ];
		
		return ( '' == $sRet ) ? $sCountryName : $sRet;
	}
	
	
	// alias
	public static function getNameFromCode( $sCode ) {
		return self::getCountryNameFromCountryCode( $sCode );
	}
	
	// $sCountry could be code or name
	public static function getContinentCodeFromCountry( $sCountry ) {
		
		self::init();
		
		$sCountry = strtoupper( trim( $sCountry ) );						// normalize
		
		return self::$aCountryContinentHash[ $sCountry ];
	}

	// $sState could be code or name
	public static function getContinentNameFromCountry( $sCountry ) {
		
		self::init();
		
		return self::$aContinents[ self::getContinentCodeFromCountry( $sCountry ) ][ 'name' ];
	}
	
	
	//
	public static function getCountryCodeFrom3Letter( $s3Letter ) {
		
		if ( NULL === self::$a3LetterCode ) {
			Geko_Geography_Xml::loadData();
		}
		
		return self::$a3LetterCode[ strtoupper( $s3Letter ) ];
	}
	
	
	
}



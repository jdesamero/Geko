<?php

//
class Geko_Geography_Xml
{
	
	public static $sFile;
	
	
	//
	public static function setFile( $sFile ) {
		self::$sFile = $sFile;
	}
	
	
	//
	public static function loadData() {
		
		if ( self::$sFile ) {
			
			$oXml = simplexml_load_file( self::$sFile );
			
			//// load continents
			
			$aContXml = $oXml->continents[ 0 ];
			
			$aContinents = array();
			$aCounTitle = array();
			foreach ( $aContXml as $oCont ) {
				
				$sAbbr = strval( $oCont[ 'abbr' ] );
				
				$aContinents[ $sAbbr ] = strval( $oCont[ 'name' ] );
				$aCounTitle[ $sAbbr ] = strval( $oCont[ 'countrylabel' ] );
			}
			
			Geko_Geography_Continent::set( $aContinents );
			
			
			//// load countries
			
			$aCounXml = $oXml->countries[ 0 ];
			
			$aCountries = array();
			$a3LetterCode = array();
			$aCountryCodeHash = array();
			
			foreach ( $aCounXml as $oCoun ) {
				
				$sCont = strval( $oCoun[ 'continent' ] );
				$sAbbr = strval( $oCoun[ 'abbr' ] );
				$sAltAbbr = strval( $oCoun[ 'altabbr' ] );
				
				$aCountries[ $sCont ][ $sAbbr ] = strval( $oCoun[ 'name' ] );
				$a3LetterCode[ $sAltAbbr ] = $sAbbr;
				
				if ( $sVaryAtt = strval( $oCoun[ 'variations' ] ) ) {
					$aVary = explode( ';', $sVaryAtt );
					foreach ( $aVary as $sVary ) {
						$aCountryCodeHash[ trim( $sVary ) ] = $sAbbr;
					}
				}
				
			}
			
			foreach ( $aContinents as $sKey => $sName ) {
				$aContinents[ $sKey ] = array(
					'title' => $aCounTitle[ $sKey ],
					'name' => $sName,
					'countries' => $aCountries[ $sKey ]
				);
			}
			
			Geko_Geography_Country::set( $aContinents, $a3LetterCode, $aCountryCodeHash );
			
			
		}
		
	}

}


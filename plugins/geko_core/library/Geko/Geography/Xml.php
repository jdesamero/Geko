<?php

//
class Geko_Geography_Xml extends Geko_Singleton_Abstract
{
	
	public $_sFile = '';
	
	
	
	//
	public function setFile( $sFile ) {
		$this->_sFile = $sFile;
	}
	
	
	//
	public function loadData() {
		
		if ( $this->_sFile ) {
			
			$oXml = simplexml_load_file( $this->_sFile );
			
			
			//// load singletons
			
			$oGeoCont = Geko_Geography_Continent::getInstance();
			$oGeoCoun = Geko_Geography_Country::getInstance();
			$oGeoState = Geko_Geography_CountryState::getInstance();
			$oGeoCity = Geko_Geography_City::getInstance();
			
			
			
			//// load continents
			
			$aContXml = $oXml->continents[ 0 ];
			
			$aContinents = array();
			$aCounTitle = array();
			foreach ( $aContXml as $oCont ) {
				
				$sAbbr = strval( $oCont[ 'abbr' ] );
				
				$aContinents[ $sAbbr ] = strval( $oCont[ 'name' ] );
				$aCounTitle[ $sAbbr ] = strval( $oCont[ 'countrylabel' ] );
			}
			
			$oGeoCont->set( $aContinents );
			
			
			//// load countries
			
			$aCounXml = $oXml->countries[ 0 ];
			
			$aCountries = array();
			$a3LetterCode = array();
			$aCountryCodeHash = array();
			$aStateLabel = array();
			
			foreach ( $aCounXml as $oCoun ) {
				
				$sCont = strval( $oCoun[ 'continent' ] );
				$sAbbr = strval( $oCoun[ 'abbr' ] );
				$sAltAbbr = strval( $oCoun[ 'altabbr' ] );
				$sStateLabel = strval( $oCoun[ 'statelabel' ] );
				
				$aCountries[ $sCont ][ $sAbbr ] = strval( $oCoun[ 'name' ] );
				$a3LetterCode[ $sAltAbbr ] = $sAbbr;
				
				if ( $sVaryAtt = strval( $oCoun[ 'variations' ] ) ) {
					$aVary = explode( ';', $sVaryAtt );
					foreach ( $aVary as $sVary ) {
						$aCountryCodeHash[ trim( $sVary ) ] = $sAbbr;
					}
				}
				
				if ( $sStateLabel ) {
					$aStateLabel[ $sAbbr ] = $sStateLabel;
				}
			}
			
			foreach ( $aContinents as $sKey => $sName ) {
				$aContinents[ $sKey ] = array(
					'title' => $aCounTitle[ $sKey ],
					'name' => $sName,
					'countries' => $aCountries[ $sKey ]
				);
			}
			
			$oGeoCoun->set( $aContinents, $a3LetterCode, $aCountryCodeHash );
			
			
			//// load states
			
			$aStateXml = $oXml->states[ 0 ];
			
			$aCounState = array();
			$aStatesGrouped = array();
			
			foreach ( $aStateXml as $oState ) {
				
				$sCountry = strval( $oState[ 'country' ] );
				$sAbbr = strval( $oState[ 'abbr' ] );
				
				$aStatesGrouped[ $sCountry ][ $sAbbr ] = strval( $oState[ 'name' ] );
			}
			
			foreach ( $aStatesGrouped as $sKey => $aStates ) {
				$aStatesGrouped[ $sKey ] = array(
					'title' => $aStateLabel[ $sKey ],
					'name' => $oGeoCoun->getNameFromCode( $sKey ),
					'states' => $aStates
				);
			}
			
			$oGeoState->set( $aStatesGrouped );
			
			
			//// load cities
			
			$aCityXml = $oXml->cities[ 0 ];
			
			$aCities = array();
			
			foreach ( $aCityXml as $oCity ) {
				
				$aCity = array( 'name' => strval( $oCity[ 'name' ] ) );
				
				if ( $sState = strval( $oCity[ 'state' ] ) ) {
					$aCity[ 'state' ] = $sState;
				}
				
				$aCity = array_merge( $aCity, array(
					'country' => strval( $oCity[ 'country' ] ),
					'lat' => floatval( strval( $oCity[ 'latitude' ] ) ),
					'lng' => floatval( strval( $oCity[ 'longitude' ] ) )
				) );
				
				$aCities[] = $aCity;
			}
			
			$oGeoCity->set( $aCities );
			
			
			
			//// unset xml
			
			unset( $oXml );
			
		}
		
	}

}


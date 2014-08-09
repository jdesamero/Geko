<?php

//
class Geko_Geography_Xml extends Geko_Geography
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
			$oGeoState = Geko_Geography_State::getInstance();
			$oGeoCity = Geko_Geography_City::getInstance();
			
			
			
			//// load continents
			
			$aContXml = $oXml->continents[ 0 ];
			
			$aContinents = array();
			foreach ( $aContXml as $oCont ) {
				
				$sAbbr = strval( $oCont[ 'abbr' ] );
				
				$aContinents[ $sAbbr ] = array(
					Geko_Geography_Continent::FIELD_NAME => strval( $oCont[ 'name' ] ),
					Geko_Geography_Continent::FIELD_COUNTRY_LABEL => strval( $oCont[ 'countrylabel' ] ),
					Geko_Geography_Continent::FIELD_DB_ID => NULL
				);
				
			}
			
			$oGeoCont->set( $aContinents );
			
			
			//// load countries
			
			$aCounXml = $oXml->countries[ 0 ];
			
			$aCountries = array();
			
			foreach ( $aCounXml as $oCoun ) {
				
				$sAbbr = strval( $oCoun[ 'abbr' ] );
				
				$aCountries[ $sAbbr ] = array(
					Geko_Geography_Country::FIELD_NAME => strval( $oCoun[ 'name' ] ),
					Geko_Geography_Country::FIELD_CONTINENT => strval( $oCoun[ 'continent' ] ),
					Geko_Geography_Country::FIELD_STATE_LABEL => strval( $oCoun[ 'statelabel' ] ),
					Geko_Geography_Country::FIELD_ALT_ABBR => strval( $oCoun[ 'altabbr' ] ),
					Geko_Geography_Country::FIELD_VARIATIONS => Geko_Array::explodeTrimEmpty( ';', strval( $oCoun[ 'variations' ] ) ),
					Geko_Geography_Country::FIELD_LATITUDE => floatval( strval( $oCoun[ 'latitude' ] ) ),
					Geko_Geography_Country::FIELD_LONGITUDE => floatval( strval( $oCoun[ 'longitude' ] ) ),
					Geko_Geography_Country::FIELD_DB_ID => NULL			// populate only when needed
				);
				
			}
			
			$oGeoCoun->set( $aCountries );
			
			
			//// load states
			
			$aStateXml = $oXml->states[ 0 ];
			
			$aStates = array();
			
			foreach ( $aStateXml as $oState ) {
				
				$sCountry = strval( $oState[ 'country' ] );
				$sAbbr = strval( $oState[ 'abbr' ] );
				
				$sCode = sprintf( '%s.%s', $sCountry, $sAbbr );
				
				$aStates[ $sCode ] = array(
					Geko_Geography_State::FIELD_NAME => strval( $oState[ 'name' ] ),
					Geko_Geography_State::FIELD_COUNTRY => $sCountry,
					Geko_Geography_State::FIELD_ABBR => $sAbbr,
					Geko_Geography_State::FIELD_VARIATIONS => Geko_Array::explodeTrimEmpty( ';', strval( $oState[ 'variations' ] ) ),
					Geko_Geography_State::FIELD_LATITUDE => floatval( strval( $oState[ 'latitude' ] ) ),
					Geko_Geography_State::FIELD_LONGITUDE => floatval( strval( $oState[ 'longitude' ] ) ),
					Geko_Geography_State::FIELD_DB_ID => NULL			// populate only when needed
				);
			}
			
			$oGeoState->set( $aStates );
			
			
			//// load cities
			
			$aCityXml = $oXml->cities[ 0 ];
			
			$aCities = array();
			
			foreach ( $aCityXml as $oCity ) {
				
				$aCities[] = array(
					Geko_Geography_City::FIELD_NAME => strval( $oCity[ 'name' ] ),
					Geko_Geography_City::FIELD_COUNTRY => strval( $oCity[ 'country' ] ),
					Geko_Geography_City::FIELD_STATE => strval( $oCity[ 'state' ] ),
					Geko_Geography_City::FIELD_VARIATIONS => Geko_Array::explodeTrimEmpty( ';', strval( $oCity[ 'variations' ] ) ),
					Geko_Geography_City::FIELD_LATITUDE => floatval( strval( $oCity[ 'latitude' ] ) ),
					Geko_Geography_City::FIELD_LONGITUDE => floatval( strval( $oCity[ 'longitude' ] ) )
				);
			}
			
			$oGeoCity->set( $aCities );
			
			
			
			//// unset xml
			
			unset( $oXml );
			
		}
		
	}

}


<?php

//
class Geko_Geography_City extends Geko_Geography
{
	
	const FIELD_NAME = 1;
	const FIELD_COUNTRY = 2;
	const FIELD_STATE = 3;
	const FIELD_VARIATIONS = 4;
	const FIELD_LATITUDE = 5;
	const FIELD_LONGITUDE = 6;
	
	
	
	protected $_aCities = NULL;
	
	
	//
	public function get() {
		return $this->getCities();
	}
	
	//
	public function getCities() {
		
		$this->init();
		
		return $this->_aCities;
	}
	
	
	//
	public function set( $aCities ) {
		
		$this->_aCities = $aCities;
		
		return $this;
	}
	
	
	//
	public function getFormatted( $aParams = array() ) {

		$this->init();
		
		$oGeoCoun = Geko_Geography_Country::getInstance();
		$oGeoState = Geko_Geography_State::getInstance();
		
		$aExclude = $aParams[ 'exclude_countries' ];
		if ( !is_array( $aExclude ) ) $aExclude = array();
		
		$aFmt = array();
		
		foreach ( $this->_aCities as $aRow ) {
			
			$sCountry = $aRow[ self::FIELD_COUNTRY ];		// country code
			
			if ( !in_array( $sCountry, $aExclude ) ) {
				
				if ( $aParams[ 'full_country_name' ] ) {
					$sCountry = $oGeoCoun->getCountryNameFromCountryCode( $sCountry );
				}
				
				if ( $sState = $aRow[ self::FIELD_STATE ] ) {
					if ( $aParams[ 'full_state_name' ] ) {
						$sState = $oGeoState->getStateNameFromStateCode(
							sprintf( '%s.%s', $aRow[ self::FIELD_COUNTRY ], $sState )
						);
					}
				}
				
				$aCity = array(
					'name' => $aRow[ self::FIELD_NAME ],
					'country' => $sCountry,
					'lat' => $aRow[ self::FIELD_LATITUDE ],
					'lng' => $aRow[ self::FIELD_LONGITUDE ]
				);
				
				if ( $sState ) {
					$aCity[ 'state' ] = $sState;
				}
				
				$aFmt[] = $aCity;
			}
		}
		
		if ( $aParams[ 'random' ] ) {
			shuffle( $aFmt );
		}
		
		return $aFmt;
	}
	

}

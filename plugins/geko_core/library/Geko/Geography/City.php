<?php

//
class Geko_Geography_City extends Geko_Singleton_Abstract
{
	
	protected $_aCities = NULL;
	
	
	//
	public function get() {
		
		if ( NULL === $this->_aCities ) {
			$oGeo = Geko_Geography_Xml::getInstance();
			$oGeo->loadData( GEKO_GEOGRAPHY_XML );
		}
		
		return $this->_aCities;
	}

	
	//
	public function set( $aCities ) {
		$this->_aCities = $aCities;
	}
	
	
	//
	public function getFormatted( $aParams = array() ) {
		
		$oGeoCoun = Geko_Geography_Country::getInstance();
		
		$this->get();
		
		$aExclude = $aParams[ 'exclude_countries' ];
		if ( !is_array( $aExclude ) ) $aExclude = array();
		
		$aFmt = array();
		
		foreach ( $this->_aCities as $aCity ) {
						
			$sCountry = $aCity[ 'country' ];		// country code
			
			if ( !in_array( $sCountry, $aExclude ) ) {

				if ( $aParams[ 'full_country_name' ] ) {
					$aCity[ 'country' ] = $oGeoCoun->getCountryNameFromCountryCode( $aCity[ 'country' ] );
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

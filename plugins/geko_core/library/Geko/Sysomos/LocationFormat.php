<?php

//
class Geko_Sysomos_LocationFormat
{
	
	protected $_sOrigLocation = '';
	
	protected $_sNormalizedLocation = '';
	protected $_sKey = '';
	protected $_bLocChanged = FALSE;
	
	protected $_fLat = NULL;
	protected $_fLon = NULL;
	
	
	
	//
	public function __construct( $sLocation ) {
		
		$this->_sOrigLocation = $sLocation;
		
		
		$sNormLocation = strtolower( trim( $sLocation, ", \t\n\r\0\x0B" ) );
		$sNormLocation = preg_replace( '/\s\s+/', ' ', $sNormLocation );
		
		
		// look for a semi-colon
		
		if ( FALSE !== strpos( $sNormLocation, ';' ) ) {
			
			// explode location into parts
			$aLocFmt = array();
			$aLoc = explode( ';', $sNormLocation );
			
			foreach ( $aLoc as $sLoc ) {
				
				$sLoc = trim( $sLoc, ", \t\n\r\0\x0B" );
				
				if ( $sLoc ) {
					
					$sLoc = str_replace( '-', ' ', $sLoc );
					$sLoc = trim( $sLoc );
					
					$aLocFmt[] = $sLoc;
				}
			}
			
			$sNormLocation = implode( ', ', $aLocFmt );
				
		}
		
		
		// make location guesses
		$aRegs = array();
		if ( preg_match( '/([0-9\.-]{3,}),.*?([0-9\.-]{3,})/', $sNormLocation, $aRegs ) ) {
			
			if ( 3 == count( $aRegs ) ) {
				
				// found latitude and longitude
				$this->_fLat = $aRegs[ 1 ];
				$this->_fLon = $aRegs[ 2 ];
			}
		}
		
		
		$this->_sNormalizedLocation = $sNormLocation;
		$this->_sKey = md5( $sNormLocation );
		
		$this->_bLocChanged = ( $sLocation != $sNormLocation ) ? TRUE : FALSE ;
		
	}
	
	
	//
	public function getInfo() {
		return array(
			'norm_location' => $this->_sNormalizedLocation,
			'lat' => $this->_fLat,
			'lon' => $this->_fLon,
			'key' => $this->_sKey,
			'loc_changed' => $this->_bLocChanged
		);
	}
	

}



<?php

//
class Geko_Google_Map
{
	
	protected $_oGquery = NULL;
	
	//
	public function __construct( $sVersion = NULL, $aParams = array() ) {
		
		if ( NULL === $sVersion ) {
			$sVersion = 'V2';
		}
		
		$sClass = sprintf( 'Geko_Google_Map_Query_%s', $sVersion );
		
		if ( class_exists( $sClass ) ) {
			$this->_oGquery = new $sClass( $aParams );
		}
	}
	
	//
	public function query( $sQuery ) {
		
		$aRes = array();
		
		if ( $oGquery = $this->_oGquery ) {
			$aRes = $oGquery->getResult( $sQuery );
		}
		
		return new Geko_Google_Map_Result( $aRes );
	}
	
}



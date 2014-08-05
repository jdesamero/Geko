<?php

//
class Geko_Google_Map
{
	
	protected $_oGquery = NULL;
	protected $_oCachedQuery = NULL;
	
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
	
	
	//
	public function cachedQuery( $sQuery, $oEngine = NULL ) {
		
		if ( !$this->_oCachedQuery ) {
			$this->_oCachedQuery = new Geko_Google_Map_CachedLookup( $this->_oGquery, $oEngine );
		}
		
		return $this->_oCachedQuery->getResult( $sQuery );
	}
	
	
	//
	public function getQueryObj() {
		return $this->_oGquery;
	}
	
	
}



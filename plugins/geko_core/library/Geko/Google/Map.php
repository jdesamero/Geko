<?php

//
class Geko_Google_Map
{
	
	protected $_sApiKey;
	protected $_sRequestUrl = 'http://maps.google.com/maps/geo';
	
	protected static $_aHttp = array();
	
	
	//
	public function __construct( $sApiKey, $sRequestUrl = NULL ) {
		
		$this->_sApiKey = $sApiKey;
		if ( NULL === $this->_sRequestUrl ) $this->_sRequestUrl = $sRequestUrl;
		
	}
	
	//
	public function getHttp() {
		
		if ( !self::$_aHttp[ $this->_sRequestUrl ] ) {			
			self::$_aHttp[ $this->_sRequestUrl ] = new Zend_Http_Client( $this->_sRequestUrl );
		}
		
		return self::$_aHttp[ $this->_sRequestUrl ];
	}
	
	//
	public function query( $sQuery ) {
		
		$aRes = array();
		
		// get coordinates
		$oHttp = $this->getHttp();
		$oHttp->setParameterGet( array(
			'key' => $this->_sApiKey,
			'sensor' => 'false',
			'output' => 'json',
			'q' => $sQuery
		) );
		
		$oResponse = $oHttp->request();
		
		if ( 200 == $oResponse->getStatus() ) {
			$aRes = Zend_Json::decode( $oResponse->getBody() );
		}
		
		return new Geko_Google_Map_QueryResult( $aRes );
		
	}
	
}



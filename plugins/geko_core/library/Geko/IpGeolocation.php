<?php

//
class Geko_IpGeolocation extends Geko_Http
{
	
	protected $_sApiKey = NULL;
	protected $_sRequestUrl = 'http://api.ipinfodb.com/v3/ip-city/';
	
	
	
	//
	public function __construct( $aParams = array() ) {
		
		if (
			( NULL === $this->_sApiKey ) && 
			( $sApiKey = $aParams[ 'api_key' ] )
		) {
			$this->_sApiKey = $sApiKey;
		}
		
	}
	
	
	
	//// functional stuff
	
	
	//
	public function getResult( $sIpAddress ) {
		
		return $this
			->_setClientUrl( NULL, array(
				'key' => $this->_sApiKey,
				'ip' => $sIpAddress,
				'format' => 'json'
			) )
			->_getParsedResponseBody()
		;
	}
	
	
		
}



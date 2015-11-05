<?php
/*
 * "geko_core/library/Geko/IpGeolocation.php"
 * https://github.com/jdesamero/Geko
 *
 * Copyright (c) 2013 Joel Desamero.
 * Licensed under the MIT license.
 */

//
class Geko_IpGeolocation extends Geko_Http
{
	
	protected $_sApiKey = NULL;
	protected $_sRequestUrl = 'http://api.ipinfodb.com/v3/ip-city/';
	
	
	
	//
	public function __construct( $aParams = array() ) {
		
		if ( $sApiKey = $aParams[ 'api_key' ] ) {
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



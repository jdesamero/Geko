<?php

// wordpress
class Geko_Wp_Integration_Client extends Geko_Integration_Client
{
	
	//
	public function __construct(
		$sRequestPath,
		Geko_Integration_Transport_Abstract $oTransport = NULL,
		Geko_Integration_Encoder_Abstract $oEncoder = NULL
	) {
		
		parent::__construct($sRequestPath, $oTransport, $oEncoder);
		
		$this->_setMimic( self::COOKIE, '/^wordpress_/i', Geko_Integration_Request::MATCH_PREG_KEY );
		// $this->_oRequest->debug();
		
	}
	
	
	//
	public function get()
	{
		return parent::get( Geko_Integration_App_Wp::CODE );
	}
	
	
}





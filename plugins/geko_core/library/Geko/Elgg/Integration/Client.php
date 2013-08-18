<?php

//
class Geko_Elgg_Integration_Client extends Geko_Integration_Client
{

	//
	public function __construct(
		$sRequestPath,
		Geko_Integration_Transport_Abstract $oTransport = NULL,
		Geko_Integration_Encoder_Abstract $oEncoder = NULL
	) {
		
		parent::__construct($sRequestPath, $oTransport, $oEncoder);
		
		$this
			->_setMimic( self::SERVER, 'HTTP_USER_AGENT' )
			->_setMimic( self::COOKIE, 'Elgg' )
		;
		
	}

	
	//
	public function get()
	{
		return parent::get( Geko_Integration_App_Elgg::CODE );
	}
	
	
}





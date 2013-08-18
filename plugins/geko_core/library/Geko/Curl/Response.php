<?php

//
class Geko_Curl_Response
{
	
	private $oClient;
	
	//
	public function __construct( $oClient ) {
		
		$this->oClient = $oClient;
		
	}
	
	//
	public function getBody() {
		return curl_exec( $this->oClient->getCurlInstance() );
	}
	
	// TO DO: implement later!!!
	public function getHeaders() {
		return '';
	}
	
}



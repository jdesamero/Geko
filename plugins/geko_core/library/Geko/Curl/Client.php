<?php

//
class Geko_Curl_Client
{
	
	private $oCurl = NULL;
	
	
	//
	public function __construct( $sUrl = '', $aParams = array() ) {
		
		$this->oCurl = curl_init();
		
		curl_setopt( $this->oCurl, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt( $this->oCurl, CURLOPT_HEADER, 0 );
		
		if ( $sUrl ) {
			curl_setopt( $this->oCurl, CURLOPT_URL, $sUrl );
		}
		
	}
	
	//
	public function request() {
		return new Geko_Curl_Response( $this );
	}
	
	//
	public function getCurlInstance() {
		return $this->oCurl;
	}
	
}



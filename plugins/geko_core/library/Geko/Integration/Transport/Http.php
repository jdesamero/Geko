<?php

//
class Geko_Integration_Transport_Http extends Geko_Integration_Transport_Abstract
{
	private $oHttpClient;
	
	
	//
	public function __construct() {
		$this->oHttpClient = new Zend_Http_Client();
	}
	
	//
	public function retrievePayload() {
		
		if ( isset($_POST['__payload']) ) {
			return base64_decode ( strrev ( $_POST['__payload'] ) );
		} else {
			return NULL;
		}
		
	}

	//
	public function retrieveEncoderClass() {
		return ( isset($_POST['__encoder']) ) ? $_POST['__encoder'] : '';
	}
	
	//
	public function _get() {
		
		return $this->oHttpClient
			->setUri( $this->processUrl( $this->_oRequest->getRequestPath() ) )
			->setParameterPost(
				'__payload',
				strrev ( base64_encode ( $this->_getEncodedRequest() ) )
			)
			->setParameterPost(
				'__encoder',
				get_class( $this->_oEncoder )
			)
			->request('POST')
			->getBody()
		;
		
	}
	
	//
	private function processUrl($sUrl) {
		return str_replace(
			'[SERVER_NAME]',
			$_SERVER['SERVER_NAME'],
			$sUrl
		);
	}
	
}



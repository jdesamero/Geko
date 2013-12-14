<?php

//
class Geko_Integration_Transport_ShellExec extends Geko_Integration_Transport_Abstract
{	
	private $sEncoderClass;
	private $sPayload;
	
	//
	public function __construct($aArgv = NULL) {
		if (NULL != $aArgv) {
			$this->sPayload = $aArgv[1];
			$this->sEncoderClass = $aArgv[2];
		}
	}
	
	
	//
	public function retrievePayload() {
		if ( $this->sPayload ) {
			return base64_decode ( strrev ( $this->sPayload ) );
		} else {
			return NULL;
		}
		
	}

	
	//
	public function retrieveEncoderClass() {
		return ( $this->sEncoderClass) ? $this->sEncoderClass : '';
	}
	
	
	//
	public function _get() {
		
		return shell_exec(
			$this->_oRequest->getRequestPath() . ' ' .
			strrev ( base64_encode ( $this->_getEncodedRequest() ) ) . ' ' .
			get_class( $this->_oEncoder )
		);
		
	}
	
}



<?php

//
abstract class Geko_Integration_Transport_Abstract extends Geko_Integration
{
	protected $_oEncoder;
	protected $_oRequest;
	
	//
	public function setEncoder(Geko_Integration_Encoder_Abstract $oEncoder) {
		$this->_oEncoder = $oEncoder;
		return $this;
	}
	
	//
	public function setRequest(Geko_Integration_Request $oRequest) {
		$this->_oRequest = $oRequest;
		return $this;
	}
	
	//
	protected function _getEncodedRequest()
	{
		return $this->_oEncoder->encode(
			$this->_oRequest
		);
	}
	
	//
	public function get() {
		return $this->_oEncoder->decode(
			$this->_get()
		);
	}
	
	
	abstract public function retrievePayload();
	
	abstract public function retrieveEncoderClass();
	
	abstract public function _get();
	
	
}



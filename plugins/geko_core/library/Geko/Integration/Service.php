<?php

//
class Geko_Integration_Service extends Geko_Integration
{
	protected $_oRequest;
	protected $_oResponse;
	
	protected $_oTransport;
	protected $_oEncoder;
	
	
	
	//
	public function __construct(
		Geko_Integration_Transport_Abstract $oTransport = NULL,
		Geko_Integration_Encoder_Abstract $oEncoder = NULL
	) {
		
		// HTTP transport is default
		if ( $oTransport ) {
			$this->setTransport( $oTransport );
		} else {
			$this->setTransport( new Geko_Integration_Transport_Http );
		}
		
		// PhpSerialize encoder is default
		if ( $oEncoder ) {
			$this->setEncoder( $oEncoder );
		} else {
			$this->setEncoder( new Geko_Integration_Encoder_PhpSerialize );
		}
		
		//
		$this->_oResponse = new Geko_Integration_Response();
		
	}
	
	
	////// accessors
	
	//// setters
	
	//
	public function setTransport(Geko_Integration_Transport_Abstract $oTransport) {
		$this->_oTransport = $oTransport;
		return $this;
	}
	
	//
	public function setEncoder(Geko_Integration_Encoder_Abstract $oEncoder) {
		$this->_oEncoder = $oEncoder;
		return $this;
	}
	
	// native call
	public function setRequest($oRequest)
	{
		$this->_oRequest = $oRequest;
		return $this;
	}

	// native call
	public function getResponse()
	{
		return $this->_oResponse;
	}
	
	
	
	//
	public function retrieveEncoder()
	{
		$sEncoderClass = $this->_oTransport->retrieveEncoderClass();
		
		if ( ('' != $sEncoderClass) && class_exists($sEncoderClass) ) {
			$oEncoder = new $sEncoderClass();
			$this->_oEncoder = $oEncoder;
		}
		
		return $oEncoder;
	}
	
	
	//
	public function retrieveRequest()
	{
		$oRequest = $this->_oEncoder->decode(
			$this->_oTransport->retrievePayload()
		);
		
		if ( $oRequest instanceof Geko_Integration_Request ) {
			$this->_oRequest = $oRequest;
		} else {
			$this->_oResponse->setStatus( Geko_Integration_Response::FAIL_REQUEST );
		}
		
		return $oRequest;
	}
	
		
	
	// $mMeta not being used yet
	public function _processRequest($mRequest, $aParams, $mMeta) {
		
		if ( is_array($mRequest) ) {
			$sClass = $mRequest[0];
			$sMethod = $mRequest[1];
		} else {
			$sClass = $mRequest;
			$sMethod = 'exec';		// default method to call
		}
		
		if ( Geko_Class::isSubclassOf($sClass, 'Geko_Integration_Service_ActionInterface') ) {
			
			return call_user_func_array( array($sClass, $sMethod), $aParams );
		
		} else {
		
			$sActionClass = get_class($this) . '_' . $sClass;
			if ( Geko_Class::isSubclassOf($sActionClass, 'Geko_Integration_Service_ActionInterface') ) {
				return call_user_func_array( array($sActionClass, $sMethod), $aParams );
			}
		
		}
		
		return NULL;
		
	}
	
	//
	public function processRequests() {
		
		foreach ($this->_oRequest->getRequestStack() as $aData) {
			list( $mRequest, $aParams, $mMeta ) = $aData;
			$this->_oResponse->setResult(
				$this->_processRequest($mRequest, $aParams, $mMeta)
			);
		}
		
		$this->_oResponse->setStatus( Geko_Integration_Response::SUCCESS );
		
		return $this;
	}
	
	//
	public function processMimic() {
		foreach ($this->_oRequest->getMimic() as $aMimic) {
			$this->_setSuperValue(
				$aMimic['type'], $aMimic['key'], $aMimic['value']
			);			
		}
		return $this;
	}
	
	
	
	//
	public function handle() {
		return $this->_oEncoder->encode( $this->_oResponse );
	}
	
	
}



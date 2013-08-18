<?php

//
class Geko_Integration_Client extends Geko_Integration
{
	const AMBIVALENT = 100;
	
	protected $_oRequest;
	protected $_oResponse;
	
	protected $_oTransport;
	protected $_oEncoder;
	
	protected $_aCallbacks = array();
	
	protected $_bTriggeredGet = FALSE;
	
	
	
	//
	public function __construct(
		$sRequestPath,
		Geko_Integration_Transport_Abstract $oTransport = NULL,
		Geko_Integration_Encoder_Abstract $oEncoder = NULL
	) {
		
		$this->_oRequest = new Geko_Integration_Request();
		$this->_oRequest->setRequestPath( $sRequestPath );
		
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
		$this->_aCallbacks = array(
			Geko_Integration_Response::FAIL_REQUEST => array(),
			Geko_Integration_Response::FAIL_RESPONSE => array(),
			Geko_Integration_Response::SUCCESS => array(),
			self::AMBIVALENT => array()
		);
		
	}
	
	
	////// accessors
	
	//// setters
	
	//
	public function setRequest( $mRequest, $aParams = array(), $mMeta = NULL ) {
		return $this->_oRequest
			->setRequest( $mRequest, $aParams, $mMeta )
			->setClient( $this )
		;
	}
	
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
	
	//
	public function _setMimicValue($iType, $sKey, $mValue) {
		$this->_oRequest->setMimicValue($iType, $sKey, $mValue);
		return $this;
	}
	
	//
	public function _setMimic($iType, $sMatchKey = '', $iMatchType = Geko_Integration_Request::MATCH_EXACT) {
		$this->_oRequest->setMimic( $iType, $sMatchKey, $iMatchType );
		return $this;
	}
	
	// these registered callbacks will be invoked after get()
	public function setCallback($mCallback, $aParams = array(), $iResponseCode = Geko_Integration_Response::SUCCESS) {
		$this->_aCallbacks[ $iResponseCode ][] = array(
			$mCallback, $aParams
		);
		return $this;
	}
	
	
	//// getters
	
	//
	/* */
	public function getRequest() {
		return $this->_oRequest;
	}
	/* */
	
	
	// should return Geko_Integration_Response
	public function get($sAppCode = '') {
		
		$oCommon = Geko_Integration_Common::getInstance();
		
		if ( ('' != $sAppCode) && ($oCommon->getAppCode() == $sAppCode) ) {
			
			// make native request
			
			// determine the corresponding service class
			$sServiceClass = str_replace( '_Client', '_Service', get_class($this) );
			
			if ( @class_exists($sServiceClass) ) {
				$oService = new $sServiceClass();
				$oResponse = $oService
					->setRequest( $this->_oRequest )
					->processRequests()
					->getResponse()
				;
			}
			
		} else {
			
			// make the remote request
			$oResponse = $this->_oTransport
				->setEncoder( $this->_oEncoder )
				->setRequest( $this->_oRequest )
				->get()
			;
			
		}

		if ( FALSE == ($oResponse instanceof Geko_Integration_Response) ) {
			$oResponse = new Geko_Integration_Response();
			$oResponse->setStatus( Geko_Integration_Response::FAIL_RESPONSE );
		}
		
		
		//
		if ( Geko_Integration_Response::SUCCESS == $oResponse->getStatus() ) {
			$oResponse->setResponseItems( $this->_oRequest );
			$this->invokeCallbackArray( $this->_aCallbacks[ Geko_Integration_Response::SUCCESS ] );
		} elseif ( Geko_Integration_Response::FAIL_REQUEST == $oResponse->getStatus() ) {
			$this->invokeCallbackArray( $this->_aCallbacks[ Geko_Integration_Response::FAIL_REQUEST ] );
		} elseif ( Geko_Integration_Response::FAIL_RESPONSE == $oResponse->getStatus() ) {
			$this->invokeCallbackArray( $this->_aCallbacks[ Geko_Integration_Response::FAIL_RESPONSE ] );
		}
		
		$this->invokeCallbackArray( $this->_aCallbacks[ self::AMBIVALENT ] );
		$this->_oResponse = $oResponse;
		
		return $this->_oResponse;
		
	}
	
	//
	public function triggerGet() {
		
		if ( !$this->_bTriggeredGet ) {
			$this->_bTriggeredGet = TRUE;
			$this->get();
		}
		
		return $this;
	}
	
	
	//// helpers
	
	//
	protected function invokeCallbackArray($aCallbacks)
	{
		foreach ($aCallbacks as $aCallback) {
			call_user_func_array( $aCallback[0], $aCallback[1] );
		}
	}
	
}



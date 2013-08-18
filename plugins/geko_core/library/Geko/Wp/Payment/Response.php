<?php

//
class Geko_Wp_Payment_Response
{

	protected $_aResponseData = NULL;
	
	protected $_sPaymentClass = '';
	protected $_sAdminClass = '';
	
	
	//
	public function __construct() {
		
		//
		$this->_sPaymentClass = Geko_Class::resolveRelatedClass(
			$this, '_Response', '', $this->_sPaymentClass
		);
		
		//
		$this->_sAdminClass = Geko_Class::resolveRelatedClass(
			$this->_sPaymentClass, '', '_Admin', $this->_sAdminClass
		);
		
	}
	
	
	
	//
	public function getPaymentInstance() {
		return Geko_Singleton_Abstract::getInstance( $this->_sPaymentClass );
	}
	
	//
	public function getAdminInstance() {
		return Geko_Singleton_Abstract::getInstance( $this->_sAdminClass );
	}
	
	
	
	//
	public function getResponseData() {
		return $this->_aResponseData;
	}
	
	
	//
	public function setResponseData( $aResponseData ) {
		$this->_aResponseData = $aResponseData;
		return $this;
	}
	
	
	//
	public function getStatusId() {
		return Geko_Wp_Payment_Admin::STATUS_UNKNOWN;
	}
	
	//
	public function getMessage() {
		return $this->_aResponseData[ 'message' ];
	}
	
}



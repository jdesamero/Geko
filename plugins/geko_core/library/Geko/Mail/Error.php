<?php

//
class Geko_Mail_Error
{

	protected $_sType;
	protected $_sMessage;
	protected $_sDetails;
	
	
	//
	public function setType( $sType ) {
		
		$this->_sType = $sType;
		
		return $this;
	}
	
	//
	public function getType() {
		return $this->_sType;
	}
	
	
	
	//
	public function setMessage( $sMessage ) {
		
		$this->_sMessage = $sMessage;
		
		return $this;
	}
	
	//
	public function getMessage() {
		return $this->_sMessage;
	}
	
	
	
	//
	public function setDetails( $sDetails ) {
		
		$this->_sDetails = $sDetails;
		
		return $this;
	}
	
	//
	public function getDetails() {
		return $this->_sDetails;
	}
	
	
	
	
}



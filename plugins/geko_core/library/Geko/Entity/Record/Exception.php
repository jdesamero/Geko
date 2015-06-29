<?php

//
class Geko_Entity_Record_Exception extends Exception
{
	
	protected $_sType = '';
	
	protected $_aTypeLabels = array(
		'db' => 'Database Error',
		'validation' => 'Validation Error',
		'default' => 'Unknown Error'
	);
	
	protected $_aErrorDetails = array();
	
	protected $_sOrigMessage = '';			// track message if problem originated from another exception
	
	
	
	
	
	//// fancy accessors
	
	//
	public function setErrorType( $sType ) {
		
		$this->_sType = $sType;
		
		return $this;
	}
	
	//
	public function setErrorDetail() {
		
		$aArgs = func_get_args();
		
		if ( is_array( $aErrors = $aArgs[ 0 ] ) ) {
			
			foreach ( $aErrors as $sKey => $sDetail ) {
				$this->setErrorDetail( $sKey, $sDetail );
			}
			
		} else {
			
			// default
			list( $sKey, $sDetail ) = $aArgs;
			
			$this->_aErrorDetails[ $sKey ] = $sDetail;
		}
		
		return $this;
	}
	
	//
	public function setOrigMessage( $sOrigMessage ) {
		
		$this->_sOrigMessage = $sOrigMessage;
		
		return $this;
	}
	
	
	
	//
	public function getErrorTypeLabel() {
		
		if ( !$sLabel = $this->_aTypeLabels[ $this->_sType ] ) {
			$sLabel = $this->_aTypeLabels[ 'default' ];
		}
		
		return $sLabel;
	}
	
	
	//
	public function getErrorDetails() {
		return $this->_aErrorDetails;
	}
	
	
	//
	public function getOrigMessage() {
		
		return $this->_sOrigMessage;
	}
	
	
}



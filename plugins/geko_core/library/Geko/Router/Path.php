<?php

//
class Geko_Router_Path
{
	
	protected $_sBaseUrl = '';
	protected $_sPath = '';
	
	protected $_sRouteName = '';
	protected $_sTarget = '';
	protected $_sSubTarget = '';
	
	protected $_aPathItems = array();
	
	protected $_aLeftovers = array();
	
	
	
	
	//
	public function __construct( $sBaseUrl ) {
		
		$this->_sBaseUrl = $sBaseUrl;
		
		$oUrl = Geko_Uri::getGlobal();
		
		// echo sprintf( '%s<br />', $oUrl->getHost() );
		// echo sprintf( '%s<br />', $oUrl->getPath() );
		
		$sPath = $oUrl->getPath();
		
		$sBaseUrlChop = str_replace( array( 'http://', 'https://' ), '', $sBaseUrl );
		
		$aRegs = array();
		
		if ( preg_match( '/\/.*/', $sBaseUrlChop, $aRegs ) ) {
			
			$sPrePath = $aRegs[ 0 ];
			
			if ( 0 === strpos( $sPath, $sPrePath ) ) {
				$sPath = substr( $sPath, strlen( $sPrePath ) );
			}
		}
		
		$this->_sPath = $sPath;
		$this->_aPathItems = Geko_Array::explodeTrimEmpty( '/', $sPath );
		
		// print_r( $this->_aPathItems );
		
	}
	
	
	//// accessors
	
	//
	public function getPath() {
		
		return $this->_sPath;
	}
	
	//
	public function getPathItems() {
		
		return $this->_aPathItems;
	}
	
	
	
	//
	public function setLeftovers( $aLeftovers ) {
		
		$this->_aLeftovers = $aLeftovers;
		
		return $this;
	}
	
	//
	public function getLeftovers() {
		
		return $this->_aLeftovers;
	}
	
	
	
	//
	public function setRouteName( $sRouteName ) {
		
		$this->_sRouteName = $sRouteName;
		
		return $this;
	}
	
	//
	public function getRouteName() {
		
		return $this->_sRouteName;
	}
	
	
	
	//
	public function setTarget( $sTarget ) {
		
		$this->_sTarget = $sTarget;
		
		return $this;
	}
	
	//
	public function getTarget() {
		
		return $this->_sTarget;
	}
	
	
	
	//
	public function setSubTarget( $sSubTarget ) {
		
		$this->_sSubTarget = $sSubTarget;
		
		return $this;
	}
	
	//
	public function getSubTarget() {
		
		return $this->_sSubTarget;
	}
	
	
	
	
	
	//// magic methods
	
	//
	public function __toString() {
		
		return $this->getPath();
	}
	
	
}




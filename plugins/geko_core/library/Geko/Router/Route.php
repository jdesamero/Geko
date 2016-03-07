<?php

//
class Geko_Router_Route
{
	
	protected $_sRouteName = '';
	
	protected $_aPrefixes = array( 'Geko_' );
	protected $_aSkip = array();
	
	protected $_oRouter;
	
	
	
	//
	public function setRouter( $oRouter, $sRouteName ) {
		
		$this->_oRouter = $oRouter;
		$this->_sRouteName = $sRouteName;
		
		return $this;
	}
	
	
	// implement by sub-class
	public function isMatch() {
		
		return FALSE;
	}
	
	
	
	//
	public function skipClass( $sCheck ) {
		
		foreach ( $this->_aPrefixes as $sPrefix ) {
			
			foreach ( $this->_aSkip as $sSuffix ) {
				
				if ( 0 === strpos( $sCheck, sprintf( '%s%s', $sPrefix, $sSuffix ) ) ) {
					
					// begins with class path to skip
					return TRUE;
				}
			}
		}
		
		return FALSE;
	}
	
	//
	public function run() { }
	
	
	
	//// helpers
	
	//
	public function getBestMatch() {
		
		$aSuffixes = func_get_args();
		
		return Geko_Class::getBestMatch( $this->_aPrefixes, $aSuffixes );
	}
	
	//
	public function getTarget() {
		
		return NULL;
	}
	
	//
	public function getRouteName() {
		
		return $this->_sRouteName;
	}
	
}



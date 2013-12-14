<?php 

// http://framework.zend.com/manual/1.12/en/zend.auth.introduction.html
class Geko_App_Auth_Storage implements Zend_Auth_Storage_Interface
{
	
	protected $_oSess;
	protected $_sValKey;
	
	//
	public function __construct( $oSess = NULL ) {
		
		if ( NULL === $oSess ) {
			$oSess = Geko_App::get( 'sess' );
		}
		
		if ( !$this->_sValKey ) {
			$this->_sValKey = md5( __CLASS__ );
		}
		
		$this->_oSess = $oSess;
	}
	
	
	//
	public function getSess() {
		
		if ( !$oSess = $this->_oSess ) {
			throw new Zend_Auth_Storage_Exception( 'There was a problem using Geko_App_Session' );
		}
		
		return $oSess;
	}
	
	//
	public function isEmpty() {
		$oSess = $this->getSess();
		return ( $oSess->get( $this->_sValKey ) ) ? FALSE : TRUE ;
	}
	
	//
	public function read() {
		$oSess = $this->getSess();
		return $oSess->get( $this->_sValKey );
	}
	
	//
	public function write( $sContents ) {
		$oSess = $this->getSess();
		return $oSess->set( $this->_sValKey, $sContents );
	}
	
	//
	public function clear() {
		$oSess = $this->getSess();
		return $oSess->delete( $this->_sValKey );
	}
	
	
}



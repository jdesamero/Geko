<?php

//
class Geko_App_Router_Route_Auth extends Geko_Router_Route
{
	
	protected $_oAuth;
	
	
	
	//
	public function __construct( $oAuth = NULL ) {
		
		if ( NULL === $oAuth ) {
			$oAuth = Geko_App::get( 'auth' );
		}
		
		$this->_oAuth = $oAuth;
	}
	
	//
	public function isMatch() {
		
		if ( !$oAuth = $this->_oAuth ) {
			return TRUE;
		}
		
		if ( !$oAuth->hasIdentity() ) {
			return TRUE;
		}
		
		return FALSE;
	}
	
	
	//
	public function run() {
		
		$oRouter = $this->_oRouter;
		
		// set AUTH_REQUIRED token
		$oRouter->setToken( 'AUTH_REQUIRED', TRUE );
		
	}
	
	
	
}
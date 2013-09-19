<?php

//
class Geko_App_Auth_Route extends Geko_Router_Route
{
		
	protected $_oAuth;
	
	protected $_sLayout = '';
	
	
	
	//
	public function __construct( $oAuth = NULL ) {
		
		if ( NULL === $oAuth ) {
			$oAuth = Geko_App::get( 'auth' );
		}

		if ( $sClass = $this->getBestMatch( 'Aux_Auth', 'Auth' ) ) {
			$this->_sDefaultLayout = $sClass;
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
		
		if ( $sBestMatch = $this->_sDefaultLayout ) {
			Geko_Singleton_Abstract::getInstance( $sBestMatch )->init();
		} else {
			throw new Exception( 'A valid template class was not found!' );
		}
		
	}
	
	
	
}
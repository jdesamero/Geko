<?php

//
class Geko_Wp_Payment_Moneris extends Geko_Wp_Payment
{
	
	//
	protected function __construct() {
		
		parent::__construct();
		
		if ( class_exists( 'mpgGlobals' ) ) {
			
			$this->_bValidLibrary = TRUE;
			
			$oAdmin = $this->getAdminInstance();
			
			$this->setGlobal( 'MONERIS_HOST', $oAdmin->getHost() );
			$this->setGlobal( 'CLIENT_TIMEOUT', $oAdmin->getClientTimeout() );
			
		}
		
	}
	
	//
	public function getGatewayId() {
		return Geko_Wp_Payment::GATEWAY_ID_MONERIS;
	}
	
	
	//// specific methods used with the Moneris Payment Gateway
	
	//
	public function setGlobal( $sKey, $sValue ) {
		if ( $this->_bValidLibrary ) {
			mpgGlobals::set( $sKey, $sValue );
		}
		return $this;
	}
	
	//
	public function setGlobals( $aParams ) {
		foreach ( $aParams as $sKey => $sValue ) {
			$this->setGlobal( $sKey, $sValue );
		}
		return $this;
	}

	//
	public function getGlobal( $sKey ) {
		if ( $this->_bValidLibrary ) {
			return mpgGlobals::get( $sKey );
		}
		return NULL;
	}
	
	//
	public function getGlobals( $sKey ) {
		if ( $this->_bValidLibrary ) {
			$aGlobals = mpgGlobals::getGlobals();
			return $aGlobals[ $sKey ];
		}
		return NULL;
	}
	
}





<?php

//
class Geko_CachedLookup_Engine_Db extends Geko_CachedLookup_Engine
{
	
	protected $_oDb;
	
	
	//
	public function __construct( $oDb = NULL ) {
		
		if ( NULL === $oDb ) {
			
			$this->_oDb = Geko::get( 'db' );
			$this->init();
		}
				
	}
	
	
	// hooks
	public function init() {
	
	}
	
	
	//
	public function getCached( $mHash, $aArgs ) {
		
		return NULL;
	}
	
	
	//
	public function saveToCache( $mHash, $aArgs, $aActRes ) {
		
		return $this;
	}
	
	
}




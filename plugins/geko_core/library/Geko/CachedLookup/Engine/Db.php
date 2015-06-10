<?php

//
class Geko_CachedLookup_Engine_Db extends Geko_CachedLookup_Engine
{
	
	protected $_oDb;
	
	protected $_sTableSignature = NULL;
	
	
	
	//
	public function __construct( $oDb = NULL ) {
		
		if ( NULL === $oDb ) {
			
			if ( $this->_oDb = Geko::get( 'db' ) ) {
				$this->init();
			}
		}
				
	}
	
	
	//
	public function init() {
		
		if ( $this->_sTableSignature ) {
			Geko_Once::run( $this->_sTableSignature, array( $this, 'createTable' ) );
		}
		
		return $this;
	}
	
	
	
	//// hooks
	
	//
	public function createTable() {
		
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




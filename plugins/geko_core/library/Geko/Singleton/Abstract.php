<?php

//
abstract class Geko_Singleton_Abstract
{
	
	public static $aSingletonInstances = array();
	
	protected $_sInstanceClass;
	
	
	
	
	
	// prevent external instantiation
	protected function __construct() {
		
		$this->_sInstanceClass = get_class( $this );
	}
	
	
	// It will always belong to the superclass
	// Unless overridden by the subclass
	public static function getInstance( $sCalledClass = '' ) {
		
		if ( '' == $sCalledClass ) {
			$sCalledClass = get_called_class();
		}
		
		if ( $sCalledClass && !self::$aSingletonInstances[ $sCalledClass ] ) {
			self::$aSingletonInstances[ $sCalledClass ] = new $sCalledClass;
		}
				
		return self::$aSingletonInstances[ $sCalledClass ];
	}
	
	//
	public static function callUserFunc( $sCalledClass, $sMethod ) {
		return call_user_func( array( self::getInstance( $sCalledClass ), $sMethod ) );
	}
	
	//
	public static function callUserFuncArray($sCalledClass, $sMethod, $aParams) {
		return call_user_func_array(
			array( self::getInstance( $sCalledClass ), $sMethod ),
			$aParams
		);
	}
	
	
	////
	
	protected $_bCalledInit = FALSE;
	
	// ensure init is called once
	public function init() {
		
		if ( !$this->_bCalledInit ) {
			
			$this->preStart();
			$this->start();
			$this->postStart();
			
			$this->_bCalledInit = TRUE;
		}
		
		return $this;
	}
	
	
	// usually pass __CLASS__ to this
	public function forceInit( $sInstanceClass ) {
		
		if ( $this->_sInstanceClass != $sInstanceClass ) {
			self::getInstance( $sInstanceClass )->init();
		}
		
		return $this;
	}
	
	
	
	// hooks
	public function preStart() { }						// ???
	public function start() { }							// ???
	public function postStart() { }						// ???
	
	
	// public function end() { }						// ???
	
	//
	public function reInit() {
		
		$aArgs = func_get_args();
		
		$this->_bCalledInit = FALSE;
		
		$this->reStart();
		
		return call_user_func_array( array( $this, 'init' ), $aArgs );
	}
	
	// hooks
	public function reStart() { }						// ???
	
	
	
}



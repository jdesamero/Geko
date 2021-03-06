<?php

//
abstract class Geko_Singleton_Abstract
{
	
	public static $aSingletonInstances = array();
	
	protected $_sInstanceClass;
	
	protected $_bCalledInit = FALSE;
	protected $_bAutoInit = FALSE;			// call init() after constructing singleton
	
	
	
	
	// It will always belong to the superclass
	// Unless overridden by the subclass
	public static function getInstance( $sCalledClass = '' ) {
		
		if ( '' == $sCalledClass ) {
			$sCalledClass = get_called_class();
		}
		
		if ( $sCalledClass && !Geko_Array::getValue( self::$aSingletonInstances, $sCalledClass ) ) {
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
	
	// prevent external instantiation
	protected function __construct() {
		
		$this->_sInstanceClass = get_class( $this );
		
		if ( $this->_bAutoInit ) {
			$this->init();
		}
		
	}
	
	
	// ensure init is called once
	public function init() {
		
		if ( !$this->_bCalledInit ) {
			
			$aArgs = func_get_args();
			
			call_user_func_array( array( $this, 'preStart' ), $aArgs );
			call_user_func_array( array( $this, 'start' ), $aArgs );
			call_user_func_array( array( $this, 'postStart' ), $aArgs );
			
			$this->_bCalledInit = TRUE;
			
			call_user_func_array( array( $this, 'afterCalledInit' ), $aArgs );
		}
		
		return $this;
	}
	
	
	//
	public function getCalledInit() {
		return $this->_bCalledInit;
	}
	
	
	// usually pass __CLASS__ to this
	public function forceInit( $sInstanceClass ) {
		
		if ( $this->_sInstanceClass != $sInstanceClass ) {
			self::getInstance( $sInstanceClass )->init();
		}
		
		return $this;
	}
	
	
	
	// hooks
	public function preStart() { }						// before start
	public function start() { }							// during start
	public function postStart() { }						// after start
	public function afterCalledInit() { }				// called right after the called init flag is set
	
	
	// public function end() { }						// ???
	
	//
	public function reInit() {
		
		$aArgs = func_get_args();
		
		$this->_bCalledInit = FALSE;
		
		call_user_func_array( array( $this, 'reStart' ), $aArgs );
		
		return call_user_func_array( array( $this, 'init' ), $aArgs );
	}
	
	// hooks
	public function reStart() { }						// called before init sequence starts all over again
	
	
	
}



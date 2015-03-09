<?php

//
class Geko_Delegate
{
	
	protected $_sDelegateClass;
	
	protected $_oSubject;
	protected $_sSubjectClass;
	
	protected $_aPlugins = array();
	
	
	
	//// static methods
	
	//
	public static function create( $sClassName, $oSubject ) {
		return new $sClassName( $oSubject );
	}
	
	//
	public static function findMatch( $aDelegates, $sMethod ) {
		
		foreach ( $aDelegates as $oDelegate ) {
			
			if (
				( method_exists( $oDelegate, $sMethod ) ) || 
				( $oDelegate->canHandleMethod( $sMethod ) )
			) {
				
				// return callback
				return array( $oDelegate, $sMethod );
			}
		}
		
		return FALSE;
	}
	
	
	
	//// instance methods
	
	//
	public function __construct( $oSubject ) {
		
		$this->_sDelegateClass = get_class( $this );
		
		$this->_oSubject = $oSubject;
		$this->_sSubjectClass = get_class( $oSubject );
		
		$this->init();
		
	}
	
	
	// init hook
	public function init() {
		return $this;
	}
	
	
	// to be implemented by sub-class
	public function canHandleMethod( $sMethod ) {
		
		return FALSE;
	}
	
	
	//// plugin methods (should be a mix-in)
	
	// common with Geko_Entity, Geko_Entity_Query, Geko_Delegate
	
	//
	public function addPlugin( $sClassName, $mParams = NULL ) {
		
		Geko_Plugin::add( $sClassName, $mParams, $this, &$this->_aPlugins, 'setupDelegate' );
		
		return $this;
	}
	
	//
	public function applyPluginFilter() {
		
		$aArgs = func_get_args();
		
		return Geko_Plugin::applyFilter( $aArgs, $this->_aPlugins );
	}
	
	//
	public function doPluginAction() {
		
		$aArgs = func_get_args();
		
		Geko_Plugin::doAction( $aArgs, $this->_aPlugins );
		
		return $this;
	}
	
	
	
	
	
	
}




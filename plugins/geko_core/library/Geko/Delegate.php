<?php

//
class Geko_Delegate
{
	
	protected $_sDelegateClass;
	
	protected $_oSubject;
	protected $_sSubjectClass;
	
	
	
	//
	public function __construct( $oSubject ) {
		
		$this->_sDelegateClass = get_class( $this );
		
		$this->_oSubject = $oSubject;
		$this->_sSubjectClass = get_class( $oSubject );
		
	}
	
	// to be implemented by sub-class
	public function canHandleMethod( $sMethod ) {
		
		return FALSE;
	}
	
	
	
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
	
	
	
}




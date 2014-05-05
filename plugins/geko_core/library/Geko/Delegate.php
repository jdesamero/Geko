<?php

//
class Geko_Delegate
{
	
	protected $_oSubject;
	
	
	
	//
	public function __construct( $oSubject ) {
		
		$this->_oSubject = $oSubject;
		
	}
	
	
	
	
	//// static methods
	
	//
	public static function create( $sClassName, $oSubject ) {
		return new $sClassName( $oSubject );
	}
	
	//
	public static function findMatch( $aDelegates, $sMethod ) {
		
		foreach ( $aDelegates as $oDelegate ) {
			
			if ( method_exists( $oDelegate, $sMethod ) ) {
				// return callback
				return array( $oDelegate, $sMethod );
			}
		}
		
		return FALSE;
	}
	
	
	
}




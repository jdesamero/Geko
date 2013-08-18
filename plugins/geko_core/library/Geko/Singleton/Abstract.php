<?php

//
abstract class Geko_Singleton_Abstract
{
	public static $aSingletonInstances = array();

	// prevent external instantiation
	protected function __construct() {
	
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
	
}



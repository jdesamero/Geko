<?php

//
class Geko_Once
{
	
	protected static $aRegistry = array();
	
	
	//
	public static function register( $sKey, $fCallback, $aArgs = array() ) {
		
		$aReg =& self::$aRegistry;
		
		if ( !$aReg[ $sKey ] ) {
			// 0: called flag | 1: callback | 2: arguments
			$aReg[ $sKey ] = array( FALSE, $fCallback, $aArgs );
		}
	}
	
	//
	public static function call( $sKey ) {
		
		$aReg =& self::$aRegistry;
		
		if ( ( $aCall = $aReg[ $sKey ] ) && ( !$aCall[ 0 ] ) ) {
			
			call_user_func_array( $aCall[ 1 ], $aCall[ 2 ] );
			
			$aReg[ $sKey ][ 0 ] = TRUE;
		}
	}
	
	// perform register() / call() in one shot
	public static function run( $sKey, $fCallback, $aArgs = array() ) {
		
		self::register( $sKey, $fCallback, $aArgs );
		self::call( $sKey );
	}
	
	//
	public static function debug() {
		// print_r( self::$aRegistry );
		print_r( array_keys( self::$aRegistry ) );
	}
	
}



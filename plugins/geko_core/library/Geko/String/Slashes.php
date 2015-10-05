<?php

class Geko_String_Slashes
{
	
	// prevent instantiation
	private function __construct() {
		// do nothing
	}
	
	//
	public static function add( $sValue ) {
		return addslashes( $sValue );
	}
	
	//
	public static function strip( $sValue ) {
		return stripslashes( $sValue );
	}
	
	//
	public static function addDeep( $mValue ) {
		
		$mValue = is_array( $mValue ) ?
			array_map( array( 'Geko_String_Slashes', 'addDeep' ), $mValue ) :
			self::add( $mValue )
		;
			
		return $mValue;
	}
	
	//
	public static function stripDeep( $mValue ) {
		
		$mValue = is_array( $mValue ) ?
			array_map( array( 'Geko_String_Slashes', 'stripDeep' ), $mValue ) :
			self::strip( $mValue )
		;
		
		return $mValue;
	}
	
}



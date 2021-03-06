<?php
/*
 * "geko_core/library/Geko/Json.php"
 * https://github.com/jdesamero/Geko
 *
 * Copyright (c) 2013 Joel Desamero.
 * Licensed under the MIT license.
 *
 * wrapper for Zend_Json
 */

//
class Geko_Json
{
	
	// 
	public static function encode() {
		
		$aArgs = func_get_args();
		
		$aArgs[ 0 ] = self::encodeFormat( $aArgs[ 0 ] );
		
		return call_user_func_array( array( 'Zend_Json', 'encode' ), $aArgs );
	}
	
	
	// format "special" values
	public static function encodeFormat( $mValue ) {
		
		if ( is_array( $mValue ) ) {
			
			foreach ( $mValue as $mKey => $mSubValue ) {
				$mValue[ $mKey ] = self::encodeFormat( $mSubValue );
			}
			
		} else if ( $mValue instanceof Geko_Json_Encodable ) {
			
			$mValue = $mValue->toJsonEncodable();
			
		}
		
		return $mValue;
	}
	
	
	//
	public static function decode() {

		$aArgs = func_get_args();
		
		return call_user_func_array( array( 'Zend_Json', 'decode' ), $aArgs );
	}
	
}



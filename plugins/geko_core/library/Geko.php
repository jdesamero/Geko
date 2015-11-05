<?php
/*
 * "geko_core/library/Geko.php"
 * https://github.com/jdesamero/Geko
 *
 * Copyright (c) 2013 Joel Desamero.
 * Licensed under the MIT license.
 */

//
class Geko
{
	
	
	protected static $_oBoot = NULL;
	
	
	
	//// static methods
	
	//
	public static function init( $oBoot ) {
		self::$_oBoot = $oBoot;
	}
	
	//
	public static function getBoot() {
		
		if ( !self::$_oBoot ) {
			self::$_oBoot = Geko_Hooks::applyFilter( sprintf( '%s::default', __METHOD__ ), NULL );
		}
		
		return self::$_oBoot;
	}
	
	//
	public static function set( $sKey, $mValue ) {
		self::getBoot()->set( $sKey, $mValue );
	}
	
	//
	public static function get( $sKey ) {
		return self::getBoot()->get( $sKey );
	}
	
	//
	public static function setVal( $sKey, $mValue ) {
		self::getBoot()->setVal( $sKey, $mValue );
	}
	
	//
	public static function getVal( $sKey ) {
		return self::getBoot()->getVal( $sKey );
	}
	
	
	
	//// utility methods
	
	// return the first non-empty value from argument list
	public static function coalesce() {
		
		$aArgs = func_get_args();		
		
		foreach ( $aArgs as $mValue ) {			
			if ( $mValue ) return $mValue;
		}
		
		return NULL;
	}
	
	
}





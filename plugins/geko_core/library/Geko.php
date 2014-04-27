<?php

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
	public static function set( $sKey, $mValue ) {
		self::$_oBoot->set( $sKey, $mValue );
	}
	
	//
	public static function get( $sKey ) {
		return self::$_oBoot->get( $sKey );
	}
	
	//
	public static function setVal( $sKey, $mValue ) {
		self::$_oBoot->setVal( $sKey, $mValue );
	}
	
	//
	public static function getVal( $sKey ) {
		return self::$_oBoot->getVal( $sKey );
	}
	
	
	
	
}





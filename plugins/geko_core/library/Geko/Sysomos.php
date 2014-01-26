<?php

//
class Geko_Sysomos
{
	
	protected static $aValues = array(
		'url.heartbeat' => 'http://api.sysomos.com/v1/heartbeat',
		'api_key' => NULL
	);
	
	
	
	//
	protected static function _getValKey( $sKey, $sPrefix ) {
		
		if ( $sPrefix ) {
			return sprintf( '%s.%s', $sPrefix, $sKey );
		}
		
		return $sKey;
	}
	
	//
	protected static function _setVal( $sKey, $sValue, $sPrefix = NULL ) {
		self::$aValues[ self::_getValKey( $sKey, $sPrefix ) ] = $sValue;
	}
	
	//
	protected static function _getVal( $sKey, $sPrefix = NULL ) {
		return self::$aValues[ self::_getValKey( $sKey, $sPrefix ) ];
	}
	
	
	//// public methods
	
	//
	public static function setValue( $sKey, $sValue ) {
		return self::_setVal( $sKey, $sValue );
	}
	
	//
	public static function getValue( $sKey ) {
		return self::_getVal( $sKey );
	}
	
	//
	public static function getUrl( $sKey ) {
		return self::_getVal( $sKey, 'url' );
	}
	
	
	
}




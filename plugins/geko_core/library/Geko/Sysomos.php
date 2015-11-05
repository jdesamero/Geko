<?php
/*
 * "geko_core/library/Geko/Sysomos.php"
 * https://github.com/jdesamero/Geko
 *
 * Copyright (c) 2013 Joel Desamero.
 * Licensed under the MIT license.
 */

//
class Geko_Sysomos
{
	
	protected static $aValues = array(
		'url.heartbeat' => '%s://api.sysomos.com/v1/heartbeat',
		'url.heartbeat_proxy' => '%s://%s/sysomosproxy/v1/heartbeat',
		'url.heartbeat_dummy' => NULL,
		'api_key' => NULL,
		'url_base' => NULL,
		'resolve_twitter' => TRUE,
		'ignore_content_filter' => FALSE
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
		
		$mValue = self::$aValues[ self::_getValKey( $sKey, $sPrefix ) ];
		
		// hard-coded transformations
		if ( 'url' == $sPrefix ) {
			
			$sProto = ( Geko_Uri::isHttps() ) ? 'https' : 'http' ;
			
			if ( 'heartbeat' == $sKey ) {
				$mValue = sprintf( $mValue, $sProto );
			} elseif ( 'heartbeat_proxy' == $sKey ) {
				$mValue = sprintf( $mValue, $sProto, self::getValue( 'url_base' ) );			
			}
		}
		
		return $mValue;
	}
	
	
	//// public methods
	
	//
	public static function setValue( $sKey, $sValue ) {
		self::_setVal( $sKey, $sValue );
	}
	
	//
	public static function getValue( $sKey ) {
		return self::_getVal( $sKey );
	}

	//
	public static function setUrl( $sKey, $sValue ) {
		self::_setVal( $sKey, $sValue, 'url' );
	}
	
	//
	public static function getUrl( $sKey ) {
		return self::_getVal( $sKey, 'url' );
	}
	
	
	
}




<?php

//
class Geko_String_Path
{
	
	protected static $sUrlRoot;
	protected static $sFileRoot;
	
	protected static $bIsHttps = FALSE;
	
	
	//// setters
	
	//
	public static function setRoots( $sUrlRoot, $sFileRoot ) {
		
		self::setUrlRoot( $sUrlRoot );
		self::setFileRoot( $sFileRoot );
	}
	
	//
	public static function setUrlRoot( $sUrlRoot ) {
		
		self::$sUrlRoot = rtrim( $sUrlRoot, '\/' );
		
		self::$bIsHttps = ( 0 === strpos( strtolower( $sUrlRoot ), 'https://' ) ) ? TRUE : FALSE ;
	}
	
	//
	public static function setFileRoot( $sFileRoot ) {
		self::$sFileRoot = rtrim( $sFileRoot, '\/' );
	}
	
	
	
	//// getters
	
	//
	public static function getUrlRoot() {
		return self::$sUrlRoot;
	}
	
	//
	public static function getFileRoot() {
		return self::$sFileRoot;
	}
		
	//
	public static function getUrlToFile( $sUrl ) {
		
		// normalize url
		if ( self::$bIsHttps ) {
			$sUrl = str_replace( 'http://', 'https://', $sUrl );
		} else {
			$sUrl = str_replace( 'https://', 'http://', $sUrl );		
		}
		
		return str_replace( self::$sUrlRoot, self::$sFileRoot, $sUrl );
	}
	
	//
	public static function getFileToUrl( $sFile ) {
		return str_replace( self::$sFileRoot, self::$sUrlRoot, $sFile );	
	}
	
	
}



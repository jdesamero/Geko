<?php

//
class Geko_File_MimeType
{
	
	//
	protected static $oMime = NULL;
	protected static $sFileCmd = '/usr/bin/file';
	protected static $sMimeTypesFile = NULL;
	
	
	
	
	//
	public static function setFileCmd( $sFileCmd ) {
		self::$sFileCmd = $sFileCmd;
	}

	//
	public static function setMimeTypesFile( $sMimeTypesFile ) {
		self::$sMimeTypesFile = $sMimeTypesFile;
	}
	
	
	
	
	//
	public static function init() {
		
		if ( NULL === self::$oMime && FALSE !== self::$oMime ) {
			if ( @class_exists( 'Mime_Types' ) ) {
				
				if ( NULL === self::$sMimeTypesFile ) {
					if ( is_file( $sDefaultMimeTypesFile = dirname( __FILE__ ) . '/mime.types' ) ) {
						self::$sMimeTypesFile = $sDefaultMimeTypesFile;
					}
				}
				
				self::$oMime = new Mime_Types( self::$sMimeTypesFile );
				self::$oMime->file_cmd = self::$sFileCmd;
				
			} else {
				self::$oMime = FALSE;
			}
		}
		
		return self::$oMime;
	}
	
	//
	public static function get( $sFilePath ) {
		
		if ( self::init() ) {
			return self::$oMime->get_file_type( $sFilePath );
		}
		
		return '';
	}
	
	
}



<?php
/*
 * "geko_core/library/Geko/File/MimeType.php"
 * https://github.com/jdesamero/Geko
 *
 * Copyright (c) 2013 Joel Desamero.
 * Licensed under the MIT license.
 */

require_once( sprintf(
	'%s/external/libs/mime_types-0.1/Mime_Types.php',
	dirname( dirname( dirname( dirname( __FILE__ ) ) ) )
) );

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
	
	
	// get extension for given mime type, if possible
	public static function getExtFromType( $sMimeType ) {
		
		if ( self::init() ) {
			return self::$oMime->get_extension( $sMimeType );
		}
		
		return '';	
	}
	
	// get mime type for given extension, if possible
	public static function getTypeFromExt( $sExt ) {
		
		if ( self::init() ) {
			return self::$oMime->get_type( $sExt );
		}
		
		return '';		
	}
	
	
	// get ext/mime type hash
	public static function getHash() {
		
		if ( self::init() ) {
			return self::$oMime->mime_types;
		}
		
		return '';		
	}
	
	
	//
	public static function isValidExt( $sExt ) {
		
		if ( self::init() ) {
			return array_key_exists( strtolower( $sExt ), self::$oMime->mime_types ) ? TRUE : FALSE ;
		}
		
		return NULL;
	}
	
	//
	public static function isValidType( $sMimeType ) {
		
		if ( self::init() ) {
			return in_array( strtolower( $sMimeType ), self::$oMime->mime_types ) ? TRUE : FALSE ;
		}
		
		return NULL;
	}
	
	
}



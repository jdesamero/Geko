<?php
/*
 * "geko_core/library/Geko/App/File/Path.php"
 * https://github.com/jdesamero/Geko
 *
 * Copyright (c) 2013 Joel Desamero.
 * Licensed under the MIT license.
 */

//
class Geko_App_File_Path extends Geko_App_Entity
{
	
	protected $_sEntityIdVarName = 'id';
	
	protected static $aPath = NULL;
	
	
	//
	public static function _initHash() {
		
		if ( NULL === self::$aPath ) {
			self::$aPath = new Geko_App_File_Path_Query( array(), FALSE );
		}
	}
	
	
	//
	public static function _sanitizePath( $sPath ) {
		
		$sPath = strtolower( rtrim( $sPath, '/' ) );
		
		if ( 0 !== strpos( $sPath, '/' ) ) {
			$sPath = sprintf( '%s/%s', GEKO_UPLOAD_PATH, $sPath );
		}
		
		return $sPath;
	}
	
	//
	public static function add( $mPath ) {
		
		self::_initHash();
		
		if ( is_array( $mPath ) ) {
			
			foreach ( $mPath as $sPath ) {
				self::add( $sPath );
			}
			
		} else {
			
			//// assuming string value of path given
			
			$sPath = self::_sanitizePath( $mPath );
			
			// check if already in db
			if ( !$oPath = self::$aPath->subsetonePath( $sPath ) ) {
				
				// do checks before committing
				if ( $sPath && is_dir( $sPath ) ) {
					
					$oPath = new Geko_App_File_Path();
					$oPath
						->setPath( $sPath )
						->save()
					;
					
					self::$aPath->addRawEntities( $oPath );
					
					return $oPath;
					
				} else {
				
					trigger_error(
						sprintf( '%s: Failed to add path, directory "%s" does not exist!', __METHOD__, $sPath ),
						E_USER_WARNING
					);
				}
				
			}
			
		}
		
	}
	
	
	//
	public static function _getId( $sPath ) {

		self::_initHash();
		
		$sPath = self::_sanitizePath( $sPath );
		
		if ( !$oPath = self::$aPath->subsetonePath( $sPath ) ) {
			
			$oPath = self::add( $sPath );
		}
		
		return ( $oPath ) ? $oPath->getId() : NULL ;
	}
	
	
	//
	public static function _getPath( $iPathId ) {
		
		if ( $oPath = self::$aPath->subsetoneId( $iPathId ) ) {
			
			return $oPath->getPath();
		}
		
		return NULL;
	}
	
	
	
}



<?php
/*
 * "geko_core/library/Geko/File.php"
 * https://github.com/jdesamero/Geko
 *
 * Copyright (c) 2013 Joel Desamero.
 * Licensed under the MIT license.
 */

//
class Geko_File
{
	//
	public static function unlink( $sFile ) {
		if ( is_file( $sFile ) ) unlink( $sFile );
	}
	
	//
	public static function getSizeFormatted( $sFile ) {
		
		if ( is_file( $sFile ) ) {
			return self::formatBytes( filesize( $sFile ) );
		}
		
		return FALSE;
	}
	
	//
	public static function formatBytes( $iBytes ) {
		
		$iKB = 1024;			// Kilobyte
		$iMB = 1024 * $iKB;		// Megabyte
		$iGB = 1024 * $iMB;		// Gigabyte
		$iTB = 1024 * $iGB;		// Terabyte
		
		$sRet = '';
		
		if ( $iBytes < $iKB ) {
			$sRet = sprintf( '%d B', $iBytes );
		} elseif ( $iBytes < $iMB ) {
			$sRet = sprintf( '%d KB', round( ( $iBytes / $iKB ), 2 ) );
		} elseif ( $iBytes < $iGB ) {
			$sRet = sprintf( '%d MB', round( ( $iBytes / $iMB ), 2 ) );
		} elseif ( $iBytes < $iTB ) {
			$sRet = sprintf( '%d GB', round( ( $iBytes / $iGB ), 2 ) );
		} else {
			$sRet = sprintf( '%d TB', round( ( $iBytes / $iTB ), 2 ) );
		}
		
		return $sRet;
	}
	
	//
	public static function isFileCoalesce() {
		
		$aArgs = func_get_args();
		$sBasePath = array_shift( $aArgs );
		
		foreach ( $aArgs as $mValue ) {
			
			if ( is_array( $mValue ) ) {
				
				$mValRec = self::isFileCoalesce( $sBasePath, $mValue );		// recursive
				if ( is_file( sprintf( '%s%s', $sBasePath, $mValRec ) ) ) return $mValRec;
				
			} else {
				
				if ( is_file( sprintf( '%s%s', $sBasePath, $mValue ) ) ) return $mValue;
			}
		}
		
		return '';
	}
	
	
	//
	public static function getUniqueName( $sFilename, $sDir ) {
		
		$aPath = pathinfo( $sFilename );
		
		$sExt = sprintf( '.%s', strtolower( $aPath[ 'extension' ] ) );
		$sFilen = strtolower( $aPath[ 'filename' ] );
		
		$sDir = rtrim( $sDir, '/' );
		
		$aMatches = array();
		
		$aFiles = scandir( $sDir );
		foreach ( $aFiles as $sFile ) {
			$sFile = strtolower( $sFile );
			
			if ( FALSE !== strpos( $sFile, $sFilen ) ) {
				
				$iNum = str_replace( array( sprintf( '%s-', $sFilen ), $sFilen, $sExt ), '', $sFile );
				if ( !$iNum || preg_match( '/^[0-9]+$/', $iNum ) ) {
					$aMatches[] = intval( $iNum );
				}
			}
		}
		
		if ( count( $aMatches ) == 0 ) return $sFilename;
		
		sort( $aMatches );
		
		foreach ( $aMatches as $i => $iNum ) {
			if ( $iNum != $i ) {
				// gap found
				return sprintf( '%s-%d.%s', $aPath[ 'filename' ], $i, $aPath[ 'extension' ] );
			}
		}
		
		return sprintf( '%s-%d.%s', $aPath[ 'filename' ], count( $aMatches ), $aPath[ 'extension' ] );
	}
	
	
	// similar to getUniqueName(), slightly different implementation
	public static function getNextAvailableFullFilePath( $sFullFilePath ) {
		
		//
		if ( file_exists( $sFullFilePath ) ) {
		
			$aPathInfo = pathinfo( $sFullFilePath );
			
			$sDirName = $aPathInfo[ 'dirname' ];
			$sFileName = $aPathInfo[ 'filename' ];
			$sExt = $aPathInfo[ 'extension' ];
			
			$sNewFileName = '';
			$iUniqueIdx = 0;
			
			do {
				
				$iUniqueIdx++;
				
				$sNewFileName = sprintf( '%s/%s.%d', $sDirName, $sFileName, $iUniqueIdx );	
				
				if ( Geko_File_MimeType::isValidExt( $sExt ) ) {
					$sNewFileName .= sprintf( '.%s', $sExt );
				}
				
			} while ( file_exists( $sNewFileName ) );
			
			
			return $sNewFileName;
		}
		
		// if file name is available, return false
		return FALSE;
	}
	
	
	
	// filter out . and ..
	public static function scandir( $sPath ) {
		
		$aFiles = scandir( $sPath );
		if ( is_array( $aFiles ) ) {
			$aFiles = array_filter( $aFiles, function( $a ) {
				return ( ( '.' == $a ) || ( '..' == $a ) ) ? FALSE : TRUE ;
			} );
		}
		
		return $aFiles;
	}
	
	
	//
	public static function requireOnceIfExists( $sFile ) {
		if ( is_file( $sFile ) ) {
			require_once( $sFile );
		}	
	}
	
	
}


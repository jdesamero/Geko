<?php

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
		
		$iKB = 1024;		// Kilobyte
		$iMB = 1024 * $iKB;	// Megabyte
		$iGB = 1024 * $iMB;	// Gigabyte
		$iTB = 1024 * $iGB;	// Terabyte
		
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
	
	
	// filter out . and ..
	public static function scandir( $sPath ) {
		
		$aFiles = scandir( $sPath );
		if ( is_array( $aFiles ) ) {
			$sFunc = create_function( '$a', 'return ( ( \'.\' == $a ) || ( \'..\' == $a ) ) ? FALSE : TRUE ;' );
			$aFiles = array_filter( $aFiles, $sFunc );
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

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
		} else {
			return FALSE;
		}
	}
	
	//
	public static function formatBytes( $iBytes ) {
		
		$iKB = 1024;		// Kilobyte
		$iMB = 1024 * $iKB;	// Megabyte
		$iGB = 1024 * $iMB;	// Gigabyte
		$iTB = 1024 * $iGB;	// Terabyte
			
		if ( $iBytes < $iKB ) {
			return $iBytes . ' B';
		} elseif ( $iBytes < $iMB ) {
			return round( ( $iBytes / $iKB ), 2 ) . ' KB';
		} elseif ( $iBytes < $iGB ) {
			return round( ( $iBytes / $iMB ), 2 ) . ' MB';
		} elseif ( $iBytes < $iTB ) {
			return round( ( $iBytes / $iGB ), 2 ) . ' GB';
		} else {
			return round( ( $iBytes / $iTB ), 2 ) . ' TB';
		}
	}
	
	//
	public static function isFileCoalesce() {
		
		$aArgs = func_get_args();
		$sBasePath = array_shift( $aArgs );
		
		foreach ( $aArgs as $mValue ) {
			if ( is_array( $mValue ) ) {
				$mValRec = self::isFileCoalesce( $sBasePath, $mValue );		// recursive
				if ( is_file( $sBasePath . $mValRec ) ) return $mValRec;
			} else {
				if ( is_file( $sBasePath . $mValue ) ) return $mValue;
			}
		}
		return '';
	}
	
	//
	public static function getUniqueName( $sFilename, $sDir ) {
		
		$aPath = pathinfo( $sFilename );
		
		$sExt = '.' . strtolower( $aPath[ 'extension' ] );
		$sFilen = strtolower( $aPath[ 'filename' ] );
		
		$sDir = rtrim( $sDir, '/' );
		
		$aMatches = array();
		
		$aFiles = scandir( $sDir );
		foreach ( $aFiles as $sFile ) {
			$sFile = strtolower( $sFile );
			if ( FALSE !== strpos( $sFile, $sFilen ) ) {
				$iNum = str_replace( array( $sFilen . '-', $sFilen, $sExt ), '', $sFile );
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
				return $aPath[ 'filename' ] . '-' . $i . '.' . $aPath[ 'extension' ];
			}
		}
		
		return $aPath[ 'filename' ] . '-' . count( $aMatches ) . '.' . $aPath[ 'extension' ];
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


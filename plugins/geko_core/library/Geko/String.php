<?php

//
class Geko_String
{
	
	// prevent instantiation
	private function __construct() {
		// do nothing
	}
	
	// DEPRECATED: Use Geko::coalesce() instead
	// return the first non-empty value from argument list
	public static function coalesce() {
		
		$aArgs = func_get_args();		
		foreach ( $aArgs as $mValue ) {
			if ( is_array( $mValue ) ) {
				$mValRec = call_user_func_array( array( __CLASS__, 'coalesce' ), $mValue );
				if ( $mValRec ) return $mValRec;
			} else {
				if ( $mValue ) return $mValue;
			}
		}
		return '';
	}
	
	
	//
	public static function truncate( $sSubject, $iLimit, $sBreak = ' ', $sPad = '...' ) {
		
		// return with no change if string is shorter than $iLimit
		if ( strlen( $sSubject ) <= $iLimit ) return $sSubject;
		
		// is $sBreak present between $iLimit and the end of the string?
		if ( FALSE !== ( $iBreakpoint = strpos( $sSubject, $sBreak, $iLimit ) ) ) {
			if ( $iBreakpoint < ( strlen( $sSubject ) - 1 ) ) {
				$sSubject = sprintf( '%s%s', rtrim( substr( $sSubject, 0, $iBreakpoint ), ',.!? ' ), $sPad );
			}
		}
		
		return $sSubject;
	}
	
	//
	public static function ptruncate( $sSubject, $iLimit, $sBreak = ' ', $sPad = '...' ) {
		echo self::truncate( $sSubject, $iLimit, $sBreak, $sPad );
	}
	
	// utf decode with extra cleanup
	public static function utfDecode( $sText ) {
		
		/* /
		$aReplace = array(
			"&#8216;",
			"&#8217;",
			'&#8220;',
			'&#8221;',
			'&mdash;',
			'&#8230;'
		);		
		/* */
		
		/* */
		$aFind = array(
			"\xe2\x80\x98",		// left single quote
			"\xe2\x80\x99",		// right single quote
			"\xe2\x80\x9c",		// left double quote
			"\xe2\x80\x9d",		// right double quote
			"\xe2\x80\x94",		// em dash
			"\xe2\x80\xa6"		// elipses
		);
		
		$aReplace = array(
			"'",
			"'",
			'"',
			'"',
			'-',
			'...'
		);
		
		return utf8_decode( str_replace( $aFind, $aReplace, $sText ) );
		/* */
		
		//return $sText;
		//return utf8_decode($sText);
		
	}
	
	//
	public static function escapeJs( $sValue ) {
		
		$aTrans = array(
			"\r" => '\r',
			"\n" => '\n',
			"\t" => '\t',
			"'"  => "\\'",
			'\\' => '\\\\'
		);
		
		return strtr( $sValue, $aTrans );
	}
	
	
	
	
	// get the fragment after the last occurance of given string
	public static function rstr( $sHaystack, $sNeedle, $iOffset = 0 ) {
		return substr( $sHaystack, strrpos( $sHaystack, $sNeedle, $iOffset ) + strlen( $sNeedle ) );
	}
	
	// implement third parameter, only present in PHP 5.3.x
	public static function strstr( $sHaystack, $sNeedle, $bBeforNeedle = FALSE ) {
		
		if ( TRUE == $bBeforNeedle ) {
			return substr( $sHaystack, 0, strpos( $sHaystack, $sNeedle ) );
		} else {
			return strstr( $sHaystack, $sNeedle );
		}
	}
	
	
	
	// extract delmitered content from a string, eg: <div></div>, or {}
	public static function extractDelimitered(
		$sSubject, $mStartDelim, $sEndDelim, $sReplace = '##%d##', $iRecursions = NULL
	) {
		
		if ( is_array( $mStartDelim ) ) {
			$sStartDelimShort = $mStartDelim[ 0 ];
			$sStartDelimLong = implode( '', $mStartDelim );
			$sStartDelim = $sStartDelimLong;
		} else {
			$sStartDelimShort = '';
			$sStartDelimLong = '';
			$sStartDelim = $mStartDelim;
		}
		
		$iOffset = $iTrack = $i = 0;
		$aSameLevel = array();
		
		while ( TRUE ) {
			
			$iCurrOffset = $iOffset;
						
			$iStartPos = stripos( $sSubject, $sStartDelim, $iOffset );
			$iEndPos = stripos( $sSubject, $sEndDelim, $iOffset );
			
			if ( ( FALSE !== $iStartPos ) || ( FALSE !== $iEndPos ) ) {
								
				if ( ( $iStartPos < $iEndPos ) && ( FALSE !== $iStartPos ) ) {
					
					// hit on a start delimiter token
					$iTrack++;
					if ( 1 == $iTrack ) {
						$aSameLevel[ $i ][ 0 ] = $iStartPos;
					}
					
					$iOffset = $iStartPos + 1;
					
					if ( $sStartDelimLong && ( $sStartDelimLong == $sStartDelim ) ) {
						$sStartDelim = $sStartDelimShort;
					}
					
				} else {
					
					// hit on a end delimiter token
					if (
						( $sStartDelimLong && ( $sStartDelimLong != $sStartDelim ) ) ||
						( !$sStartDelimLong )
					) {
						$iTrack--;
						
						if ( 0 == $iTrack ) {
							$aSameLevel[ $i ][ 1 ] = $iEndPos;			
							$i++;	// advance counter
						}
					}
					
					$iOffset = $iEndPos + 1;
				}
				
				if ( ( 0 == $iTrack ) && $sStartDelimLong ) {
					$sStartDelim = $sStartDelimLong;
				}
				
			}
			
			// the offset did not change, so kill the loop
			if ( $iCurrOffset == $iOffset ) break;
		}
		
		$iStartDelimLen = strlen( $sStartDelim );
		$iEndDelimLen = strlen( $sEndDelim );
		
		$aChunks = array();
		
		// get the chunks
		foreach ( $aSameLevel as $aPair ) {
			$sChunk = substr( $sSubject, $aPair[ 0 ], $aPair[ 1 ] - $aPair[ 0 ] + $iEndDelimLen );
			$aChunks[] = $sChunk;
		}
	
		// ensure chunks are unique
		$aChunks = array_unique( $aChunks );
		
		$sReturn = $sSubject;
		
		// replace subject with placeholders
		foreach ( $aChunks as $i => $sChunk ) {
			
			$sReturn = str_replace( $sChunk, sprintf( $sReplace, $i ), $sReturn );
			
			if ( NULL === $iRecursions || $iRecursions > 0 ) {
				// recursively apply to chunks
				$iRecursions = ( NULL === $iRecursions ) ? NULL : $iRecursions - 1;
				
				$sReChunk = substr( $sChunk, $iStartDelimLen, strlen( $sChunk ) - $iEndDelimLen - $iStartDelimLen );
				$aChunks[ $i ] = self::extractDelimitered(
					$sReChunk, $sStartDelim, $sEndDelim, $sReplace, $iRecursions
				);
				
				if ( is_array( $aChunks[ $i ] ) ) {
					$aChunks[ $i ][ 0 ] = sprintf( '%s%s%s', $sStartDelim, $aChunks[ $i ][ 0 ], $sEndDelim );		// re-introduce the delimiter
				} else {
					$aChunks[ $i ] = $sChunk;												// no change
				}
			}
			
		}
		
		// return a result
		if ( count( $aChunks ) > 0 ) {
			// return an array with two elements:
			//	0: $sSubject replaced with placeholders
			//	1: $aChunks corresponding to the placeholders
			return array( $sReturn, $aChunks );
		} else {
			// no change
			return $sSubject;
		}
	}
	
	
	//
	public static function inList( $sNeedle, $mHaystack, $sDelim = ',' ) {
		
		if ( !is_array( $mHaystack ) ) {
			$mHaystack = Geko_Array::explodeTrim( $sDelim, $mHaystack );
		}
		
		return in_array( $sNeedle, $mHaystack );
	}
	
	
	// arg 1 is a sprintf pattern
	// if arg2 is not empty then fill the pattern
	// otherwise, return ''
	// example using $ notation:
	// Geko_String::sw( '<span id="%s$1">%s$0</span>', 'Some Name', 'some-id' )
	public static function sprintfWrap() {
		
		$aArgs = func_get_args();
		if ( $aArgs[ 1 ] ) {
			
			$aRegs = array();
			if ( preg_match_all( '/\$[0-9]+/', $aArgs[ 0 ], $aRegs ) ) {
				$aReArgs = array();
				$sPattern = $aArgs[ 0 ];
				foreach ( $aRegs[ 0 ] as $sOrder ) {
					$sPattern = str_replace( $sOrder, '', $sPattern );		// strip it
					$iIndex = intval( str_replace( '$', '', $sOrder ) );
					$aReArgs[] = $aArgs[ $iIndex + 1 ];
				}
				array_unshift( $aReArgs, $sPattern );
				$aArgs = $aReArgs;
			}
			
			return call_user_func_array( 'sprintf', $aArgs );
		} else {
			return '';
		}	
	}
	
	// alias to sprintfWrap
	public static function sw() {		
		$aArgs = func_get_args();
		return call_user_func_array( array( __CLASS__, 'sprintfWrap' ), $aArgs );
	}
	
	// echo version of sprintfWrap
	public static function printfWrap() {
		$aArgs = func_get_args();
		echo call_user_func_array( array( __CLASS__, 'sprintfWrap' ), $aArgs );
	}
	
	// echo version of sprintfWrap
	public static function pw() {
		$aArgs = func_get_args();
		echo call_user_func_array( array( __CLASS__, 'sprintfWrap' ), $aArgs );
	}
	
	
	// recursive
	public static function stripSlashesDeep( $mValue ) {
		
		$mValue = is_array( $mValue ) ?
			array_map( array( __CLASS__, 'stripSlashesDeep' ), $mValue ) : 
			stripslashes( $mValue )
		;
		
		return $mValue;
	}
	
	
	//
	public static function printNumberFormat() {
		$aArgs = func_get_args();
		echo call_user_func_array( 'number_format', $aArgs );
	}
	
	
	// get string value from output buffer, based on provided callback and arguments
	// WARNING!!! Protected and private methods will not work!!!
	public static function fromOb( $mCallback, $aArgs = array() ) {
		
		ob_start();
		call_user_func_array( $mCallback, $aArgs );
		$sOutput = ob_get_contents();
		ob_end_clean();
		
		return $sOutput;
	}
	
	
	// trim multi-byte whitespace characters
	public static function mbTrim( $sValue ) {
		return trim( preg_replace( '/^[\pZ\pC]+|[\pZ\pC]+$/u', ' ', $sValue ) );
	}
	
	
	//
	public static function firstMatch( $sSubject, $aMatch ) {
		
		foreach ( $aMatch as $sMatch ) {
			if ( FALSE !== strpos( $sSubject, $sMatch ) ) {
				return $sMatch;
			}
		}
		
		return FALSE;
	}
	
	//
	public static function hasMatch( $sSubject, $aMatch ) {
		
		foreach ( $aMatch as $sMatch ) {
			if ( FALSE !== strpos( $sSubject, $sMatch ) ) {
				return TRUE;
			}
		}
		
		return FALSE;
	}
	
	//
	public static function replacePlaceholders( $aPlaceholders, $sContent ) {

		foreach ( $aPlaceholders as $sKey => $mValue ) {
			$sPhValue = sprintf( '##%s##', $sKey );
			if ( FALSE !== strpos( $sContent, $sPhValue ) ) {
				$mValue = ( is_array( $mValue ) ) ? implode( ', ', $mValue ) : $mValue ;
				$sContent = str_replace( $sPhValue, $mValue, $sContent );
			}
		}		
		
		return $sContent;
	}
	
	
	// replace first match only
	public static function replaceFirstMatch( $sNeedle, $sReplace, $sHaystack ) {
		
		$iPos = strpos( $sHaystack, $sNeedle );
		if ( FALSE !== $iPos ) {
			$sHaystack = substr_replace( $sHaystack, $sReplace, $iPos, strlen( $sNeedle ) );
		}
		
		return $sHaystack;
	}
	
	
}



<?php

class Geko_Array
{
	
	//
	public static function keyExists( $sKey, $aSubject ) {
		if ( !is_array( $aSubject ) ) return FALSE;
		return array_key_exists( $sKey, $aSubject );
	}
	
	//
	public static function in( $sValue, $aSubject ) {
		if ( !is_array( $aSubject ) ) return FALSE;
		return in_array( $sValue, $aSubject );
	}
	
	//
	public static function inPreg( $sValue, $aPatterns ) {
		
		foreach ( $aPatterns as $sPattern ) {
			
			if ( 0 === strpos( $sPattern, '/' ) ) {
				
				// regex pattern
				if ( preg_match( $sPattern, $sValue ) ) return TRUE;
				
			} else {
				
				// regular string
				if ( $sValue == $sPattern ) return TRUE;
			}
		}
		
		return FALSE;
	}
	
	
	//
	public static function implode( $sDelim, $aSubject ) {
		if ( !is_array( $aSubject ) ) return '';
		return implode( $sDelim, $aSubject );
	}
	
	//
	public static function hasAtLeastOneItem( $aSubject ) {
		if ( !is_array( $aSubject ) ) return FALSE;
		return ( count( $aSubject ) > 0 ) ? TRUE : FALSE;
	}
	
	// return TRUE if value begins with any of the subjects
	public static function beginsWith( $sValue, $aSubject ) {
		
		if ( !is_array( $aSubject ) ) return FALSE;
		
		foreach ( $aSubject as $sSubject ) {
			if ( 0 === strpos( $sValue, $sSubject ) ) {
				return TRUE;
			}
		}
		
		return FALSE;
	}
	
	// return TRUE if any of the subjects is contained in the value
	public static function contains( $sValue, $aSubject ) {
		
		if ( !is_array( $aSubject ) ) return FALSE;
		
		foreach ( $aSubject as $sSubject ) {
			if ( FALSE !== strpos( $sValue, $sSubject ) ) {
				return TRUE;
			}
		}
		
		return FALSE;
	}
	
	
	// wrap a scalar value as an array, if not already an array
	public static function wrap( $mValue ) {
		return ( is_array( $mValue ) ) ? $mValue : array( $mValue );
	}
	
	
	// return the chopped elements off subject
	public static function chop( &$aSubject, $iNum ) {
		
		$aRet = array_slice( $aSubject, 0, $iNum );
		$aSubject = array_slice( $aSubject, $iNum );
		
		return $aRet;
	}
	
	
	
	// Get the corresponding element in an array, based on the given string index
	//    ie: $sKeys: '[1]' or '[foo]' or '[att][val][30]
	//    and get corresponding element from array
	public static function getElement( $aValues, $sKeys ) {
		
		$aKeys = self::parseKeys( $sKeys );
		
		$mReturnValue = $aValues;
	
		foreach( $aKeys as $sKey ) {
			if ( TRUE == is_array( $mReturnValue ) ) {
				$mReturnValue = $mReturnValue[ $sKey ];
			} else {
				// there is no need to continue
				//$mReturnValue = FALSE;			// this caused problems
				break;
			}
		}
		
		return $mReturnValue;
	}
	
	//
	public static function setElement( &$aValues, $sKeys, $mValue ) {
		
		$aKeys = self::parseKeys($sKeys);
		
		// traverse 'all' the keys
		foreach( $aKeys as $sKey ) {
			
			if ( FALSE == is_array( $aValues ) ) {
				$aValues = array( $sKey => '' );
			}
			
			// by reference !!!
			$aValues =& $aValues[ $sKey ];
		}
		
		// set
		$aValues = $mValue;
	}
	
	//
	public function unsetElement( &$aValues, $sKeys ) {
		
		$aKeys = self::parseKeys( $sKeys );
		$iKeyCount = count( $aKeys );
		
		// traverse 'all' the keys
		for ( $i = 0; $i < $iKeyCount; $i++ ) {
			
			$sKey = $aKeys[ $i ];
			
			if ( $i == ( $iKeyCount - 1 ) ) {
				// hit last element
				unset( $aValues[ $sKey ] );
			} else {
				if ( FALSE == isset( $aValues[ $sKey ] ) ) {
					// bad key supplied
					break;
				} else {
					// by reference !!!
					$aValues =& $aValues[ $sKey ];
				}
			}
		}
	}
	
	// change string '[a][b][c]...' to
	// array('a', 'b', 'c');
	public static function parseKeys( $sKeys ) {
		
		$aKeys = explode( '[', $sKeys );
		$aKeysNoGaps = array();
		
		foreach( $aKeys as $sValue ) {
			$sValue = str_replace( ']', '', $sValue );			
			if ( '' != $sValue ) $aKeysNoGaps[] = $sValue;
		}
		
		return $aKeysNoGaps;
	}
	
	
	// break string 'key[a][b]...' to array('key', '[a][b]');
	// if 'key' then array('key', '');
	public static function parseVarName( $sVarName ) {
		
		// extract 
		if ( TRUE == preg_match( '/([[:alnum:]_]*)(\[.*)/', $sVarName, $aRegs ) ) {
			// reference to an array
			// ie: "file[a][b]"
			// $aRegs[1] -> 'file'; $aRegs[2] -> '[a][b]'
			return array( $aRegs[ 1 ], $aRegs[ 2 ] );
		} else {
			return array( $sVarName, '' );
		}
	}
	
	
	// flatten a multi-dimensional array
	// indexes become, eg: [foo[att][val][30]]
	public static function flatten( $aSubject, $sParentKey = '', &$aReturn = array() ) {
		if ( is_array( $aSubject ) ) {
			foreach ( $aSubject as $sKey => $mChild ) {
				if ( TRUE == is_array( $mChild ) ) {
					self::flatten( $mChild, self::flattenGetKey( $sParentKey, $sKey ), $aReturn );
				} else {
					$aReturn[ self::flattenGetKey( $sParentKey, $sKey ) ] = $mChild;
				}
			}
		}
		return $aReturn;
	}
	
	// construct the flattened key for the function above
	private static function flattenGetKey( $sParentKey, $sKey ) {
		if ( '' == $sParentKey ) {
			return $sKey;
		} else {
			return $sParentKey . '[' . $sKey . ']';
		}
	}
	
	// do a deep merge of elements
	public static function merge() {
		
		$aArgs = func_get_args();
		$aMerged = array();
		
		foreach ( $aArgs as $mArg ) {
			if ( !is_array( $mArg ) ) $mArg = array( $mArg );			
			$mArg = self::flatten( $mArg );
			$aMerged = array_merge( $mArg, $aMerged );
		}
		
		return $aMerged;
	}
	
	
	// create a normalized hash from an array of keys
	public static function createNormalizedHashFromKeys( $aKeys, $fNormalizeCallback = 'strtolower' ) {
		$aHash = array();
		foreach ( $aKeys as $sValue ) {
			$aHash[ call_user_func( $fNormalizeCallback, $sValue ) ] = $sValue;
		}
		return $aHash;
	}
	

	// create a normalized hash from an array of keys
	public static function normalizeParams( $aParams, $aParamKeys, $fNormalizeCallback = 'strtolower' ) {
		
		$aNormalized = array();
		if ( !is_array( $aParams ) ) $aParams = array();
		
		foreach ( $aParams as $sKey => $mValue ) {
			$aNormalized[ $aParamKeys[ call_user_func( $fNormalizeCallback, $sKey ) ] ] = $mValue;
		}
		
		return $aNormalized;
	}
	
	//
	public static function isAssoc( $aSubject ) {
		return (
			is_array( $aSubject ) && 
			0 !== count( array_diff_key(
				$aSubject,
				array_keys(
					array_keys( $aSubject )
				)
			) )
		);
	}
	
	//
	public static function explodeTrim( $sDelim, $sSubject, $aParams = array() ) {
		
		$iLimit = ( $aParams[ 'limit' ] ) ? $aParams[ 'limit' ] : NULL;
		
		$fTrimFunc = 'trim';
		if ( $sTrimChars = $aParams[ 'trim_chars' ] ) {
			
			$sTrimChars = str_replace(
				array( '"', '##ws##' ),
				array( '\"', ' \t\n\r\0\x0B' ),
				$sTrimChars
			);
			
			$fTrimFunc = create_function(
				'$sSubject',
				'return trim( $sSubject, "' . $sTrimChars . '" );'
			);
		}
		
		$aRet = array_map(
			$fTrimFunc,
			( NULL !== $iLimit ) ? 
				explode( $sDelim, $sSubject, $iLimit ) : 
				explode( $sDelim, $sSubject )
		);
		
		if ( $aParams[ 'remove_empty' ] ) {
			$aRet = array_diff(
				$aRet,
				( $aParams[ 'empty_filter' ] ) ? 
					$aParams[ 'empty_filter' ] : 
					array( '' )
			);
			$aRet = array_values( $aRet );
		}
		
		return $aRet;
	}
	
	//
	public static function explodeTrimEmpty( $sDelim, $sSubject, $aParams = array() ) {
		$aParams[ 'remove_empty' ] = TRUE;
		return self::explodeTrim( $sDelim, $sSubject, $aParams );
	}
	
	// return items in $aSubject only present in $aKeys
	public static function sanitize( $aSubject, $aKeys ) {
		
		$aRet = array();
		
		foreach ( $aKeys as $sKey ) {
			if ( isset( $aSubject[ $sKey ] ) ) {
				$aRet[ $sKey ] = $aSubject[ $sKey ];
			}
		}
		
		return $aRet;
	}
	
	//
	public static function pushUnique( &$aSubject, $mValue ) {
		if ( !in_array( $mValue, $aSubject ) ) {
			$aSubject[] = $mValue;
		}
		return $aSubject;
	}
	
	
	//
	public function levelize( $aRows, $iParentId = NULL, $iLevel = 0, $aParams = array() ) {
		
		$aParams = array_merge( array(
			'id_key' => 'id',
			'parent_key' => 'parent_id',
			'level_key' => 'level'
		), $aParams );
		
		$sIdKey = $aParams[ 'id_key' ];
		$sParentKey = $aParams[ 'parent_key' ];
		$sLevelKey = $aParams[ 'level_key' ];
		
		$aResFmt = array();
		
		foreach ( $aRows as $aRow ) {
			if ( $aRow[ $sParentKey ] == $iParentId ) {
				
				$aRow[ $sLevelKey ] = $iLevel;
				$aResFmt[] = $aRow;
				
				$aChildren = self::levelize( $aRows, $aRow[ $sIdKey ], $iLevel + 1, $aParams );
				
				if ( is_array( $aChildren ) ) {
					$aResFmt = array_merge( $aResFmt, $aChildren );
				}
			}
		}
		
		if ( count( $aResFmt ) > 0 ) {
			return $aResFmt;
		}
		
		return NULL;
	}
	
	
	// http://ca2.php.net/shuffle
	public static function shuffle( $aSubject ) {
		
		if ( !is_array( $aSubject ) ) {
			return $aSubject;
		}
		
		$aRandom = array();
		$aKeys = array_keys( $aSubject );
		
		shuffle( $aKeys );
		
		foreach ( $aKeys as $sKey ) {
			$aRandom[ $sKey ] = $aSubject[ $sKey ];
		}
		
		return $aRandom;
	}
	
	
	// insert the given key/value pair before matchKey
	// if no match was found, then append to the end
	public static function insertBeforeKey( $aSubject, $sMatchKey, $sKey, $mValue = NULL ) {
		
		$aRes = array();
		$bMatch = FALSE;
		
		foreach ( $aSubject as $sMyKey => $mMyValue ) {
			
			if ( $sMyKey == $sMatchKey ) {
				$aRes[ $sKey ] = $mValue;
				$bMatch = TRUE;
			}
			
			$aRes[ $sMyKey ] = $mMyValue;
		}
		
		if ( !$bMatch ) $aRes[ $sKey ] = $mValue;
		
		return $aRes;		
	}
	
	
	
}



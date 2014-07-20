<?php

class Geko_Uri
{
	
	//
	protected $_sUri;
	protected $_bMutable = TRUE;
	
	protected $_aParsed = array();
	
	
	
	//
	public function __construct( $sUri = '', $bMutable = TRUE ) {
		
		if ( $sUri ) {
			$this->_sUri = $sUri;
		} else {
			// use the current URL if none is specified
			$this->_sUri = self::getFullCurrent();
		}
		
		$this->_bMutable = $bMutable;
		
		$this->_aParsed = self::parse( $this->_sUri );
	}
	
	
	
	
	
	//// generic
	
	//
	public function has( $sKey ) {
		return ( isset( $this->_aParsed[ $sKey ] ) );
	}
	
	//
	public function get( $sKey ) {
		if ( $this->has( $sKey ) ) {
			return $this->_aParsed[ $sKey ];
		} else {
			return NULL;
		}
	}
	
	//
	public function same( $sKey, Geko_Uri $oUri ) {
		
		$sValue = $this->get( $sKey );
		
		if ( FALSE !== strpos( $sValue, '*' ) ) {
			// perform wildcard matching
			return preg_match(
				self::makePatternMatch( $sValue ),
				$oUri->get( $sKey )
			) ? TRUE : FALSE ;
		} else {
			// perform straight matching
			return ( $sValue == $oUri->get( $sKey ) );		
		}
	}
	
	
	
	//// path
	
	//
	public function setPath( $sPath, $bSetIfEmpty = TRUE ) {
		
		if ( $this->_bMutable ) {
			if (
				( $bSetIfEmpty ) || 
				( !$bSetIfEmpty && $sPath )
			) {
				$this->_aParsed[ 'path' ] = $sPath;
			}
		} else {
			throw new Exception( 'This object is not mutable (eg: set* methods cannot be used).' );
		}
		
		return $this;
	}
	
	
	
	//// host
	
	//
	public function setHost( $sHost, $bSetIfEmpty = TRUE ) {
		
		if ( $this->_bMutable ) {
			if (
				( $bSetIfEmpty ) || 
				( !$bSetIfEmpty && $sHost )
			) {
				$this->_aParsed[ 'host' ] = $sHost;
			}
		} else {
			throw new Exception( 'This object is not mutable (eg: set* methods cannot be used).' );
		}
		
		return $this;
	}
	
	
	
	//// vars
	
	//
	public function hasVar( $sKey ) {
		return (
			( isset( $this->_aParsed[ 'vars' ] ) ) &&
			( isset( $this->_aParsed[ 'vars' ][ $sKey ] ) )
		);
	}
	
	//
	public function getVar( $sKey ) {
		if ( $this->hasVar( $sKey ) ) {
			return $this->_aParsed[ 'vars' ][ $sKey ];
		} else {
			return NULL;		
		}
	}
	
	//
	public function getVars() {
		return $this->_aParsed[ 'vars' ];
	}
	
	//
	public function getVarCount() {
		return count( $this->_aParsed[ 'vars' ] );	
	}
	
	
	//
	public function setVar( $sKey, $mValue, $bSetIfEmpty = TRUE ) {
		
		if ( $this->_bMutable ) {
			if (
				( $bSetIfEmpty ) || 
				( !$bSetIfEmpty && $mValue )
			) {
				$this->_aParsed[ 'vars' ][ $sKey ] = $mValue;
			}
		} else {
			throw new Exception( 'This object is not mutable (eg: set* methods cannot be used).' );
		}
		
		return $this;
	}
	
	
	//
	public function setVars( $aVars ) {
		
		if ( $this->_bMutable ) {
			$this->_aParsed[ 'vars' ] = array_merge( $this->_aParsed[ 'vars' ], $aVars );	
		} else {
			throw new Exception( 'This object is not mutable (eg: set* methods cannot be used).' );
		}

		return $this;
	}
	
	
	//
	public function unsetVar( $sKey ) {
		
		if ( $this->_bMutable ) {
			unset( $this->_aParsed[ 'vars' ][ $sKey ] );
		} else {
			throw new Exception( 'This object is not mutable (eg: set* methods cannot be used).' );
		}

		return $this;
	}
	
	//
	public function unsetVars() {
		
		if ( $this->_bMutable ) {
			unset( $this->_aParsed[ 'vars' ] );
			$this->_aParsed[ 'vars' ] = array();
		} else {
			throw new Exception( 'This object is not mutable (eg: set* methods cannot be used).' );
		}

		return $this;	
	}
	
	
	// compare
	public function sameVar( $sKey, Geko_Uri $oUri ) {
		return ( $this->getVar( $sKey ) == $oUri->getVar( $sKey ) );	
	}
	
	// compare matching vars
	public function sameVars( Geko_Uri $oUri, $bStrict = FALSE, $sIgnoreVars = '' ) {
		
		// if strict mode check that value being compared has no vars
		if ( $bStrict ) {
			
			$aCompareVars = $oUri->getVars();
			
			// remove vars to ignore from comparison
			if ( $sIgnoreVars ) {
				$aIgnoreVars = Geko_Array::explodeTrim( ',', $sIgnoreVars );
				foreach ( $aIgnoreVars as $sKey ) {
					if ( isset( $aCompareVars[ $sKey ] ) ) unset( $aCompareVars[ $sKey ] );
				}
			}
			
			// perform strict comparison check
			return (
				( count( array_diff( $this->_aParsed[ 'vars' ], $aCompareVars ) ) > 0 ) || 
				( count( array_diff( $aCompareVars, $this->_aParsed[ 'vars' ] ) ) > 0 )
			) ? FALSE : TRUE;
		}
		
		// $sVar is not used
		foreach ( $this->_aParsed[ 'vars' ] as $sKey => $sVar ) {
			// one of the values don't match
			if ( !$this->sameVar( $sKey, $oUri ) ) return FALSE;
		}
		
		return TRUE;
	}
	
	//
	public function getVarsAsHiddenFields() {
		
		$sOut = '';

		$aFlatVars = Geko_Array::flatten( $this->_aParsed[ 'vars' ] );
		foreach ( $aFlatVars as $sKey => $sValue ) {
			$sOut .= sprintf(
				'<input type="hidden" name="%s" value="%s" />%s',
				$sKey,
				htmlspecialchars( $sValue ),
				"\n"
			);
		}
		
		return $sOut;
	}
	


	//// output methods
	
	//
	public function getServer() {
		
		$a = $this->_aParsed;
		
		$sOut =  
			( ( $a[ 'scheme' ] ) ? sprintf( '%s://', $a[ 'scheme' ] ) : '' ) .
			( ( $a[ 'user' ] ) ? sprintf( '%s:%s@', $a[ 'user' ], $a[ 'pass' ] ) : '' ) .
			$a[ 'host' ] .
			( ( $a[ 'port' ] ) ? sprintf( ':%d', $a[ 'port' ] ) : '' )
		;
		
		return $sOut;	
	}
	
	
	//
	public function __toString() {
		
		$a = $this->_aParsed;
		
		$aFlatVars = Geko_Array::flatten( $a[ 'vars' ] );
		$aGather = array();
		foreach ( $aFlatVars as $sKey => $sValue ) {
			$aGather[] = sprintf( '%s=%s', $sKey, urlencode( $sValue ) );
		}
				
		$sFlatVars = implode( '&', $aGather );
		
		$sOut =  
			$this->getServer() .
			$a[ 'path' ] .
			( ( $sFlatVars ) ? sprintf( '?%s', $sFlatVars ) : '' ) .
			( ( $a[ 'fragment' ] ) ? sprintf( '#%s', $a[ 'fragment' ] ) : '' )
		;
		
		return $sOut;
	}


	
	
	//// static methods
	
	//
	public static function parse( $sUri ) {
		
		$aParsed = array();
		
		/* possible values:
			scheme		( uri -> parse_url )
			host		( uri -> parse_url )
			port		( uri -> parse_url )
			user		( uri -> parse_url )
			pass		( uri -> parse_url )
			path		( uri -> parse_url )
			query		( uri -> parse_url )
			fragment	( uri -> parse_url )
			dirname		( path -> dirname )
			basename	( path -> basename )
			extension	( path -> extension )
			filename	( path -> filename )
			vars		( query -> parse_str )
		*/
		
		if ( $sUri ) {
			
			$aParsed = parse_url( $sUri );
			
			if ( isset( $aParsed[ 'path' ] ) ) {
				$aParsed = array_merge( $aParsed, pathinfo( $aParsed[ 'path' ] ) );
			}
			
			if ( isset( $aParsed[ 'query' ] ) ) {
				$aVars = array();
				parse_str( $aParsed[ 'query' ], $aVars );
				$aParsed[ 'vars' ] = $aVars;
			}
			
			if ( !is_array( $aParsed[ 'vars' ] ) ) $aParsed[ 'vars' ] = array();
		}
		
		return $aParsed;
	}
	
	
	//
	public static function makePatternMatch( $sPath ) {
		
		// escape special chars and replace wildcards
		$sPath = str_replace( array( '/', '.', '*' ), array( '\/', '\.', '.*' ), $sPath );
		
		// check if last char is a backslash and make it optional
		if ( '/' == substr( $sPath, strlen($sPath) - 1 ) ) {
			$sPath .= '?';
		}
		
		return sprintf( '/^%s$/si', $sPath );
	}
	
	
	//// http://code-better.com/php/get-current-url
	
	// get base url
	// TO DO: figure out base sub-directory
	public static function getBase() {
		
		$sPath = '';
		
		if ( $_SERVER[ 'SERVER_PROTOCOL' ] ) {
		
			// 's' if HTTPS
			list( $sProtocol, $sProtVersion ) = explode( '/', strtolower( $_SERVER[ 'SERVER_PROTOCOL' ] ) );
			
			if ( 'on' == $_SERVER[ 'HTTPS' ] ) $sProtocol = 'https';
			
			$sPath .= sprintf( '%s://', $sProtocol );
			
			// Server name
			$sPath .= $_SERVER[ 'SERVER_NAME' ];
			
			// Non-standard ports
			if ( 
				( ( 'https' == $sProtocol ) && ( $_SERVER[ 'SERVER_PORT' ] != '443' ) ) ||
				( ( 'http' == $sProtocol ) && ( $_SERVER[ 'SERVER_PORT' ] != '80' ) )
			) {
				$sPath .= sprintf( ':%d', $_SERVER[ 'SERVER_PORT' ] );
			}
			
		}
		
		return $sPath;
	}
	
	// get full current url
	public static function getFullCurrent() {
		
		$sPath = '';
		
		if ( $sPath = self::getBase() ) {
			$sPath .= $_SERVER[ 'REQUEST_URI' ];
		}
		
		return $sPath;
	}
	
	
	
	//// global instance of current Url, meant for comparison only so must be immutable
	
	protected static $_bInitGlobalUrl = FALSE;
	protected static $_oGlobalUrl;
	
	//
	public static function getGlobal() {
		
		if ( !self::$_bInitGlobalUrl ) {
			$sClass = __CLASS__;
			self::$_oGlobalUrl = new $sClass( '', FALSE );
			self::$_bInitGlobalUrl = TRUE;
		}
		
		return self::$_oGlobalUrl;
	}
	
	//// global url registry
	
	protected static $_aUrls = array();
	
	//
	public static function setUrl() {
		$aArgs = func_get_args();
		if ( is_array( $aArgs[ 0 ] ) ) {
			self::$_aUrls = array_merge( self::$_aUrls, $aArgs[ 0 ] );
		} else {
			self::$_aUrls[ $aArgs[ 0 ] ] = $aArgs[ 1 ];		
		}
	}
	
	//
	public static function getUrl( $sKey ) {
		return self::$_aUrls[ $sKey ];
	}
	
	//
	public static function getUrls() {
		return self::$_aUrls;
	}
	
	
	
	//// utility methods
	
	// http://stackoverflow.com/questions/1175096/how-to-find-out-if-you-are-using-https-without-serverhttps
	// check if the current url is https
	public static function isHttps() {
		return (
			!empty( $_SERVER[ 'HTTPS' ] ) && 
			( 'off' !== $_SERVER[ 'HTTPS' ] ) || 
			( 443 == $_SERVER[ 'SERVER_PORT' ] )
		) ? TRUE : FALSE ;
	}
	
	
	//
	public static function forceHttps() {
		
		if ( !self::isHttps() ) {
			$oUrl = self::getGlobal();
			$sUrl = str_replace( 'http://', 'https://', strval( $oUrl ) );
			header( sprintf( 'Location: %s', $sUrl ) );
		}
		
	}
	
	
	
	//// magic methods
	
	//
	public function __call( $sMethod, $aArgs ) {
		
		//
		if ( 0 === strpos( $sMethod, 'has' ) ) {
			
			$sPart = strtolower( substr_replace( $sMethod, '', 0, 3 ) );
			return $this->has( $sPart );
			
		} elseif ( 0 === strpos( $sMethod, 'get' ) ) {
			
			$sPart = strtolower( substr_replace( $sMethod, '', 0, 3 ) );
			return $this->get( $sPart );
			
		} elseif ( 0 === strpos( $sMethod, 'same' ) ) {
			
			$sPart = strtolower( substr_replace( $sMethod, '', 0, 4 ) );
			return $this->same( $sPart, $aArgs[ 0 ] );
			
		}
		
		throw new Exception( sprintf( 'Invalid method %s::%s() called.', get_class( $this ), $sMethod ) );
	}

	
	
	
	
}



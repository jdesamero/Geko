<?php

require_once( sprintf(
	'%s/external/libs/scssphp/scss.inc.php',
	dirname( dirname( dirname( __FILE__ ) ) )
) );

//
class Geko_Scss
{
	
	protected static $sSourceDir;
	protected static $sCacheDir;
	
	
	protected $_sFileName;
	protected $_sDirName;
	protected $_sAltCacheDir;
	
	
	//
	protected static function addTrailingDirSep( $sPath ) {
		
		if ( DIRECTORY_SEPARATOR != substr( $sPath, strlen( $sPath ) - 1 ) ) {
			// add a trailing '/'
			$sPath .= DIRECTORY_SEPARATOR;
		}
		
		return $sPath;
	}
	
	//
	public static function setCacheDir( $sCacheDir ) {
		self::$sCacheDir = self::addTrailingDirSep( $sCacheDir );
	}
	
	//
	public static function setSourceDir( $sSourceDir ) {		
		self::$sSourceDir = self::addTrailingDirSep( $sSourceDir );
	}
	
	//
	public static function paramCoalesce( $aParams, $sKeyList ) {
		
		$aArgs = array();
		$aKeyList = explode( '|', $sKeyList );
		
		foreach ( $aKeyList as $sKey ) {
			if ( isset( $aParams[ $sKey ] ) ) $aArgs[] = $aParams[ $sKey ];
		}
		
		return ( count( $aArgs ) > 0 ) ?
			call_user_func_array( array( 'Geko_String', 'coalesce' ), $aArgs ) : 
			NULL
		;
	}
	
	
	
	//// constructor
	
	//
	public function __construct( $aParams = array() ) {
		
		$aParams = Geko_Hooks::applyFilter( __METHOD__, $aParams, $this );
		
		$this
			->arrSetFullPath( $aParams, 'fp|full|fullpath' )
			->arrSetFileName( $aParams, 'fn|fname|filename' )
			->arrSetDirName( $aParams, 'd|dir|dirname' )
			->arrSetAltCacheDir( $aParams, 'acd|altcd|altcachedir' )
		;
	}
	
	
	//// accessors
	
	//
	public function setFullPath( $sFullPath ) {
		
		$this
			->setFileName( basename( $sFullPath ) )
			->setDirName( dirname( $sFullPath ) )
		;
		
		return $this;
	}
	
	//
	public function setFileName( $sFileName ) {
		
		$this->_sFileName = $sFileName;
		
		return $this;
	}
	
	//
	public function setDirName( $sDirName ) {
		
		$this->_sDirName = $sDirName;
		
		return $this;
	}
	
	//
	public function setAltCacheDir( $sAltCacheDir ) {
		
		$this->_sAltCacheDir = $sAltCacheDir;
		
		return $this;
	}
	
	
	//
	public function output() {
		
		$sSourceDir = Geko_String::coalesce( $this->_sDirName, self::$sSourceDir );
		$sCacheDir = Geko_String::coalesce( $this->_sAltCacheDir, self::$sCacheDir );
		
		$oServe = new Geko_Scss_Server( $sSourceDir, $sCacheDir );
		
		$oServe->setInputFile( $this->_sFileName );
		
		$oServe->serve();
		
	}
	
	
	
	//// helpers
	
	// a CSS file is generated from the SCSS file
	public function buildCssUrl( $sCssUrl = '', $bRetObj = FALSE ) {
		
		if ( !$sCssUrl ) {
			$sCssUrl = Geko_Uri::getUrl( 'geko_scss' );
		}
		
		$oUrl = new Geko_Uri( $sCssUrl );
		$oUrl
			->setVar( 'fn', $this->_sFileName, FALSE )
			->setVar( 'd', $this->_sDirName, FALSE )
			->setVar( 'acd', $this->_sAltCacheDir, FALSE )
		;
		
		$oUrl = Geko_Hooks::applyFilter( __METHOD__, $oUrl, $this );
		
		return ( $bRetObj ) ? $oUrl : strval( $oUrl ) ;
	}

	
	//// magic methods
	
	//
	public function __call( $sMethod, $aArgs ) {
		
		//
		if ( 0 === strpos( $sMethod, 'arrSet' ) ) {
			
			// attempt to call set*() method if it exists
			$sCall = substr_replace( $sMethod, 'set', 0, 6 );
			
			if ( method_exists( $this, $sCall ) ) {
				
				$mRes = self::paramCoalesce( $aArgs[ 0 ], $aArgs[ 1 ] );
				
				if ( NULL !== $mRes ) {
					return $this->$sCall( $mRes );
				} else {
					return $this;
				}
			}
			
		}
		
		throw new Exception( sprintf( 'Invalid method %s::%s() called.', get_class( $this ), $sMethod ) );
	}
	
	
	
}




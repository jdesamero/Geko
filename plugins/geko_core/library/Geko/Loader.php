<?php

require_once 'Zend/Loader/Autoloader.php';

//
class Geko_Loader extends Zend_Loader
{
	protected static $bInit = FALSE;
	
	protected static $sLibRoot = '';
	
	
	// init
	public static function init() {
		// call once
		if ( !self::$bInit ) {
			
			require_once( dirname( __FILE__ ) . '/functions.inc.php' );
			
			$oAutoloader = Zend_Loader_Autoloader::getInstance();
			$oAutoloader->pushAutoloader( array( __CLASS__, 'autoloadNsLevelClass' ) );
			
			self::$bInit = TRUE;
		}
	}
	
	// autoload namespace level classes
	public static function autoloadNsLevelClass( $sClass ) {
		
		$oAutoloader = Zend_Loader_Autoloader::getInstance();
		$aNs = $oAutoloader->getRegisteredNamespaces();
				
		if ( in_array( $sClass . '_', $aNs ) ) {
			$aDirs = explode( PATH_SEPARATOR, get_include_path() );
			foreach ( $aDirs as $sDir ) {
				$sFile = $sDir . DIRECTORY_SEPARATOR . $sClass . '.php';
				if ( is_file( $sFile ) ) {
					require_once $sFile;
					break;
				}
			}
		}
	}
	
	
	
	// add the specified dirs to ini.include_path
	public static function addIncludePaths() {
		$aDirs = func_get_args();
		$sIncludePath = ini_get( 'include_path' ) . self::appendPaths( $aDirs );		
		ini_set( 'include_path', $sIncludePath );
	}
	
	//
	public static function setLibRoot( $sLibRoot ) {
		self::$sLibRoot = $sLibRoot;
	}
	
	//
	public static function addLibRootPaths() {
		
		$aPaths = func_get_args();
		
		$aDirs = array();
		foreach ( $aPaths as $sPath ) {
			$aDirs[] = realpath( self::$sLibRoot . $sPath );
		}
		
		$sIncludePath = ini_get( 'include_path' ) . self::appendPaths( $aDirs );		
		ini_set( 'include_path', $sIncludePath );
	}
		
	// recursively append paths from a multi-dimensional array
	private static function appendPaths( $aDirs ) {
		
		$sIncludePath = '';
		
		foreach ( $aDirs as $mElem ) {
			
			if ( TRUE == is_array( $mElem ) ) {	
				// append to include path, with PATH_SEPARATOR delimiter
				$sIncludePath .= self::appendPaths( $mElem );
			} elseif ( '' != ( $mElem = realpath( $mElem ) ) ) {
				$sIncludePath .= PATH_SEPARATOR . $mElem;
			}
		}
		
		return $sIncludePath;
	}
	
	// do a batch require_once() on an array of files
	public static function batchRequireOnce( $aFiles ) {
		foreach ( $aFiles as $sFile ) {
			require_once( $sFile );
		}	
	}
	
	//
	public static function registerAutoload( $class = 'Zend_Loader', $enabled = TRUE ) {
		self::init();
		parent::registerAutoload( $class, $enabled );
	}
	
	//
	public static function registerNamespaces() {
		
		self::init();
		
		$oAutoloader = Zend_Loader_Autoloader::getInstance();
		$aArgs = func_get_args();
		foreach ( $aArgs as $sNamespace ) {
			$oAutoloader->registerNamespace( $sNamespace );
		}
	}
	
	
	
}



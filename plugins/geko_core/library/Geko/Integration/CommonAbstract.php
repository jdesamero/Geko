<?php

//
class Geko_Integration_CommonAbstract extends Geko_Singleton_Abstract
{
	const PERSIST = TRUE;
	
	protected $aRegisteredApps = array();
	protected $aDefaultApp = array( 'Geko_Integration_App_Default' => array() );
	
	protected $oCurrentApp;
	
	protected $sDir;
	protected $sUrl = '/common';
	
	protected $aData = array();
	
	
	
	//
	public function init()
	{
		static $bCalled = FALSE;
		
		if ( FALSE == $bCalled ) {
			
			@session_start();
			if ( FALSE == is_array( $_SESSION['Geko_Integration_Common'] ) ) {
				$_SESSION['Geko_Integration_Common'] = array();
			}
			
			
			$aApps = array_merge( $this->aRegisteredApps, $this->aDefaultApp );
			foreach ($aApps as $sAppClassName => $aParams) {
				if ( class_exists($sAppClassName) ) {
					$oApp = new $sAppClassName( $aParams );
					//echo $sAppClassName . '<br />';
					if (
						($oApp instanceof Geko_Integration_App_Abstract) &&
						( $oApp->detect() )
					) {
						$this->oCurrentApp = $oApp;
						break;
					}
				}
			}
			
			// ensure that $this->oCurrentApp is an object
			if ( FALSE == is_object( $this->oCurrentApp ) ) {
				$this->oCurrentApp = new Geko_Integration_App_Default();
			}
			
			// include common init file, if it exists
			$this->includeFile('init');
			
			// shutdown
			register_shutdown_function( array($this, 'shutdown') );
			
			$bCalled = TRUE;
			
		}
		
		return $this;
	}
	
	//
	public function shutdown()
	{
		// include common shutdown file, if it exists
		$this->includeFile('shutdown');	
	}
	
	//// accessors
	
	//
	public function registerDefaultApp( $sAppClassName, $aParams = array() )
	{
		$this->aDefaultApp[ $sAppClassName ] = $aParams;
		return $this;
	}
	
	//
	public function registerApp( $sAppClassName, $aParams = array() )
	{
		$this->aRegisteredApps[ $sAppClassName ] = $aParams;
		return $this;
	}
		
	
	//
	public function setDir($sDir)
	{
		$this->sDir = $sDir;
		return $this;
	}

	//
	public function getDir()
	{
		return $this->sDir;
	}
	
	//
	public function setUrl($sUrl)
	{
		$this->sUrl = $sUrl;
		return $this;
	}

	//
	public function getUrl()
	{
		return $this->sUrl;
	}
	
	//
	public function isUsed()
	{
		return ( count($this->aRegisteredApps) > 0 );
	}
	
	
	
	// simple registry accessors
	
	//
	public function set($sKey, $mValue, $bPersist = FALSE)
	{
		if ( $bPersist ) {
			$_SESSION['Geko_Integration_Common'][$sKey] = $mValue;
		} else {
			$this->aData[$sKey] = $mValue;
		}
		return $this;
	}

	//
	public function get($sKey)
	{
		if ( $this->_has($sKey, self::PERSIST) ) {
			return $_SESSION['Geko_Integration_Common'][$sKey];		
		} elseif ( $this->_has($sKey) ) {
			return $this->aData[$sKey];
		} else {
			return NULL;
		}
	}
	
	//
	public function _has($sKey, $bPersist = FALSE)
	{
		if ( $bPersist ) {
			return ( array_key_exists($sKey, $_SESSION['Geko_Integration_Common']) );
		} else {
			return ( array_key_exists($sKey, $this->aData) );
		}		
	}
	
	//
	public function has($sKey)
	{
		return ( $this->_has($sKey) || $this->_has($sKey, self::PERSIST) );
	}
	
	//
	public function _unset($sKey)
	{
		if ( $this->_has($sKey, self::PERSIST) ) {
			unset( $_SESSION['Geko_Integration_Common'][$sKey] );
		} elseif ( $this->_has($sKey) ) {
			unset( $this->aData[$sKey] );
		}
		return $this;
	}
	
	
	// convenience methods

	//
	public function getCurrentApp()
	{
		return $this->oCurrentApp;
	}

	//
	public function getAppCode()
	{
		return $this->oCurrentApp->getCode();
	}
	
	//
	public function getAppKey()
	{
		return $this->oCurrentApp->getKey();
	}
	
	//
	public function getAppSubdir()
	{
		return $this->oCurrentApp->getSubdir();
	}
	
	//
	public function getAppDbConn()
	{
		return $this->oCurrentApp->getDbConn();
	}
	
	
	
	//// 
	
	// include files
	
	//
	public function includeFile($sInclude)
	{
		$sFullIncludeInitPath = $this->sDir . '/includes/' . $sInclude . '_init.inc.php';
		if ( is_file($sFullIncludeInitPath) ) {
			include( $sFullIncludeInitPath );
		}
		
		$sFullIncludePath = $this->sDir . '/includes/' . $sInclude . '.inc.php';
		if ( is_file($sFullIncludePath) ) {
			include( $sFullIncludePath );
		}
		
		return $this;
	}
	
	//
	public function styleSheet($sStyle, $sMedia = 'screen')
	{
		$sShortPath = '/styles/' . $sInclude . '.css';
		$sFullStylePath = $this->sDir . $sShortPath;
		if ( is_file($sFullStylePath) ) {
			$this->renderStyleSheetTags( $this->sUrl . $sShortPath, $sMedia );
		}
		
		return $this;
	}
	
	
	// style sheets
	
	//
	public function autoStyleSheets($sMedia = 'screen')
	{
		//// common auto styles
		$sCommonStylePath = Geko_File::isFileCoalesce(
			$this->sDir,
			'/' . $sMedia . '.css',
			( $sMedia == 'screen' ) ? '/style.css' : ''
		);
		
		if ( $sCommonStylePath ) {
			$this->renderStyleSheetTags( $this->sUrl . $sCommonStylePath, $sMedia );
		}

		//// app code styles
		$sAppCodeStylePath = Geko_File::isFileCoalesce(
			$this->sDir,
			'/styles/' . $sMedia . '/' . $this->getAppCode() . '.css',
			( $sMedia == 'screen' ) ? '/styles/' . $this->getAppCode() . '.css' : ''
		);
		
		if ( $sAppCodeStylePath ) {
			$this->renderStyleSheetTags( $this->sUrl . $sAppCodeStylePath, $sMedia );
		}

		//// app key styles
		$sAppKeyStylePath = Geko_File::isFileCoalesce(
			$this->sDir,
			'/styles/' . $sMedia . '/' . $this->getAppKey() . '.css',
			( $sMedia == 'screen' ) ? '/styles/' . $this->getAppKey() . '.css' : ''
		);
		
		// do not duplicate if $sAppKeyStylePath is the same as $sAppCodeStylePath
		if ( $sAppKeyStylePath && ( $sAppKeyStylePath != $sAppCodeStylePath ) ) {
			$this->renderStyleSheetTags( $this->sUrl . $sAppKeyStylePath, $sMedia );
		}
		
		//// app sub-directory styles
		$sAppSubdirStylePath = Geko_File::isFileCoalesce(
			$this->sDir,
			'/styles/' . $sMedia . '/' . $this->getAppSubdir() . '.css',
			( $sMedia == 'screen' ) ? '/styles/' . $this->getAppSubdir() . '.css' : ''
		);
		
		// do not duplicate if $sAppSubdirStylePath is the same as $sAppCodeStylePath
		if ( $sAppSubdirStylePath && ( $sAppSubdirStylePath != $sAppCodeStylePath ) ) {
			$this->renderStyleSheetTags( $this->sUrl . $sAppSubdirStylePath, $sMedia );
		}
				
		return $this;		
	}
	
	
	// image
	
	//
	public function getImageUrl($sImage)
	{
		$sImageFile = Geko_File::isFileCoalesce(
			$this->sDir . '/images',
			'/' . $sImage,
			'/' . $sImage . '.gif',
			'/' . $sImage . '.jpg',
			'/' . $sImage . '.jpeg',
			'/' . $sImage . '.png'
		);
		
		return $this->sUrl . '/images' . $sImageFile;
	}
	
	
	//// helper functions
	
	//
	protected function renderStyleSheetTags($sHref, $sMedia)
	{
		?>
		<link rel="stylesheet" href="<?php echo $sHref; ?>" type="text/css" media="<?php echo $sMedia; ?>" />
		<?php
		
		return $this;
	}
	
	//
	public static function forward($sLocation) {
		header ('Location: ' . $sLocation);
		die();	
	}	
	
	
	// make sure stuff inside script tags are enclosed in <!-- -->
	public static function fixScriptTags( $sContent )
	{
		$sContent = preg_replace(
			'/<script(.*?)>(.*?)<\/script>/si',
			'<script\\1><!--\\2--></script>',
			$sContent
		);
		
		return str_replace(
			array(
				'<!---->',
				'<!--<!--',
				'<!--<!--'
			),
			array(
				'',
				'<!--<!--',
				'<!--'
			),
			$sContent
		);	
	}
	
	
	
	//
	public function test()
	{
		// print_r( $this->aRegisteredApps );
		// print_r( $this->aDefaultApp );
		print_r( array_merge( $this->aRegisteredApps, $this->aDefaultApp ) );
		print_r( $this->oCurrentApp );
	}
	
	
}




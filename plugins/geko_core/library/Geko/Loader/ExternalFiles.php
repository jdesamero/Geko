<?php

//
class Geko_Loader_ExternalFiles extends Geko_Singleton_Abstract
{
	
	const SCRIPT_TYPE = 1;
	const STYLE_TYPE = 2;
	// const FOO_TYPE = 3;		// other context?
	
	
	protected $_bInit = FALSE;
	
	protected $_aRegistered = array();
	protected $_aEnqueued = array();

	protected $_aMergeParams = array();
	
	protected $_aXmlConfigTypes = array( 'script', 'style' );
	
	protected $_sBaseUrl = '';
	protected $_sCurUrl = '';
	
	
	
	//// init, accessors
	
	//
	public function init() {
		
		if ( !$this->_bInit ) {
			
			if ( !$this->_sBaseUrl ) {
				$this->_sBaseUrl = Geko_Uri::getBase();
				$this->_sCurUrl = Geko_Uri::getFullCurrent();
			}
			
			$this->_bInit = TRUE;
		}
		
		return $this;
	}
	
	//
	public function setBaseUrl( $sBaseUrl ) {
		
		$this->_sBaseUrl = $sBaseUrl;
		
		return $this;
	}	
	
	//
	public function setCurUrl( $sCurUrl ) {
		
		$this->_sCurUrl = $sCurUrl;
		
		return $this;
	}
	
	//
	public function setMergeParam( $sKey, $mValue ) {

		$this->_aMergeParams[ $sKey ] = $mValue;
		
		return $this;
	}
	
	//
	public function setMergeParams( $aMergeParams ) {
		
		$this->_aMergeParams = array_merge(
			$this->_aMergeParams,
			$aMergeParams
		);
		
		return $this;
	}
	
	
	
	//// main methods
	
	//
	public function register( $iType, $sId, $aParams ) {
		
		$this->init();
		$this->_aRegistered[ $iType ][ $sId ] = $aParams;
		
		return $this;
	}
	
	//
	public function enqueue( $iType, $sId ) {
		
		if ( !is_array( $this->_aEnqueued[ $iType ] ) ) {
			$this->_aEnqueued[ $iType ] = array();
		}
		
		if (
			( !in_array( $sId, $this->_aEnqueued[ $iType ] ) ) && 
			( array_key_exists( $sId, $this->_aRegistered[ $iType ] ) )
		) {
			// queue the dependencies first, if any
			
			$aItem = $this->_aRegistered[ $iType ][ $sId ];
			$aDependencies = $aItem[ 'dependencies' ];
			
			if ( is_array( $aDependencies ) ) {
				foreach ( $aDependencies as $sDependency ) {
					$this->enqueue( $iType, $sDependency );
				}
			}
			
			$this->_aEnqueued[ $iType ][] = $sId;			
		}
		
		return $this;
	}
	
	//
	public function renderTags( $iType, $fCallback ) {
		
		$aQueue = $this->_aEnqueued[ $iType ];
		
		foreach ( $aQueue as $sId ) {
			$aItem = $this->_aRegistered[ $iType ][ $sId ];
			call_user_func( $fCallback, $aItem );
		}
	}
	
	//
	public function modifyFileUrl( $sFile ) {
		
		if ( Geko_Array::beginsWith( $sUrl, array( 'http:', 'https:' ) ) ) {
			
			if ( 0 === strpos( $sFile, '/' ) ) {
				// absolute path
				$sFile = sprintf( '%s%s', $this->_sBaseUrl, $sFile );		
			} else {
				// relative path
				$sFile = sprintf( '%s/%s', $this->_sCurUrl, $sFile );			
			}
		}
		
		return $sFile;
	}
	
	
	
	//// type wrappers
	
	//
	public function registerScript( $sId, $aParams ) {
		return $this->register( self::SCRIPT_TYPE, $sId, $aParams );
	}
	
	//
	public function enqueueScript( $sId ) {
		return $this->enqueue( self::SCRIPT_TYPE, $sId );
	}
	
	
	//
	public function registerStyle( $sId, $aParams ) {
		return $this->register( self::STYLE_TYPE, $sId, $aParams );
	}
	
	//
	public function enqueueStyle( $sId ) {
		return $this->enqueue( self::STYLE_TYPE, $sId );
	}	
	
	
	//
	public function renderScriptTags() {
		
		Geko_Hooks::doAction( sprintf( '%s::pre', __METHOD__ ), $this );
		
		$this->renderTags( self::SCRIPT_TYPE, array( $this, 'renderScriptTag' ) );
		
		return $this;
	}
	
	public function renderScriptTag( $aItem ) {
		
		$aAtts = array(
			'type' => 'text/javascript',
			'src' => $this->modifyFileUrl( $aItem[ 'file' ] )
		);
		
		echo strval( _ge( 'script', $aAtts ) );
		echo "\n";
	}
	
	
	//
	public function renderStyleTags() {
		
		Geko_Hooks::doAction( sprintf( '%s::pre', __METHOD__ ), $this );
		
		$this->renderTags( self::STYLE_TYPE, array( $this, 'renderStyleTag' ) );
		
		return $this;
	}
	
	//
	public function renderStyleTag( $aItem ) {
		
		$aAtts = array(
			'rel' => 'stylesheet',
			'type' => 'text/css',
			'href' => $this->modifyFileUrl( $aItem[ 'file' ] )
		);
		
		if ( $sMedia = $aItem[ 'media' ] ) {
			$aAtts[ 'media' ] = $sMedia;
		}
		
		echo strval( _ge( 'link', $aAtts ) );
		echo "\n";
	}
	
	
	
	
	
	
	//// register from an XML config file
	
	//
	public function registerFromXmlConfigFile( $sFile, $aCallbacks = NULL ) {
		
		$oReg = simplexml_load_file( $sFile );
		
		// default callbacks
		if ( !$aCallbacks ) {
			$aCallbacks = array(
				'script' => array( $this, 'registerScript' ),
				'style' => array( $this, 'registerStyle' )
			);
		}
		
		$aVersionConst = array(
			'jquery' => 'JQUERY_VERSION',
			'jquery-ui' => 'JQUERY_UI_VERSION'
		);
		
		$aVersionFlags = array_filter( explode( ' ', trim( $oReg[ 'version-flags' ] ) ) );
		$aUseVersion = array();
		
		// determine the version to use for particular flag
		foreach ( $aVersionFlags as $sFlag ) {
			
			$sDefVerKey = sprintf( 'default-%s-version', $sFlag );
			$sUseVersion = trim( $oReg[ $sDefVerKey ] );
			$sConst = $aVersionConst[ $sFlag ];
			
			if ( $sConst && defined( $sConst ) ) $sUseVersion = constant( $sConst );
			if ( $sUseVersion ) $aUseVersion[ $sFlag ] = $sUseVersion;
		}
		
		//
		foreach ( $this->_aXmlConfigTypes as $sType ) {
			
			$sTag = sprintf( '%ss', $sType );
			$fCallback = $aCallbacks[ $sType ];
			
			// make sure function exists
			if ( !is_callable( $fCallback ) ) continue;
			
			if ( !$aFile = $oReg->$sTag->file ) {
				continue;
			}
			
			foreach ( $aFile as $oItem ) {
								
				$bContinue = TRUE;
				
				// check the version flags and ensure correct file version is loaded
				foreach ( $aUseVersion as $sFlag => $sUseVersion ) {
					$sVerKey = sprintf( '%s-version', $sFlag );		
					$sVersion = trim( $oItem[ $sVerKey ] );
					if ( $sVersion && ( $sUseVersion != $sVersion ) ) {
						$bContinue = FALSE;
						break;
					}
				}
				
				if ( $bContinue ) {

					$sFile = trim( $oItem[ 'src' ] );
				
					$aRegs = array();
					if ( preg_match( '/##([A-Za-z-_]+)##/', $sFile, $aRegs ) ) {
						
						$sSearch = $aRegs[ 0 ];
						$sKey = $aRegs[ 1 ];
						$sReplace = FALSE;
						
						if ( defined( $sKey ) ) {
							$sReplace = constant( $sKey );
						} elseif ( array_key_exists( $sKey, $this->_aMergeParams ) ) {
							$sReplace = $this->_aMergeParams[ $sKey ];
						}
						
						if ( $sReplace ) {
							$sFile = str_replace( $sSearch, $sReplace, $sFile );
						}
					}
					
					$sId = trim( $oItem[ 'id' ] );
					
					$aParams = array( 'file' => $sFile );
					
					if ( $sDeps = trim( $oItem[ 'dependencies' ] ) ) {
						$aParams[ 'dependencies' ] = array_filter( explode( ' ', $sDeps ) );
					}
					
					if (
						( 'style' == $sType ) && 
						( $sMedia = trim( $oItem[ 'media' ] ) )
					) {
						$aParams[ 'media' ] = $sMedia;
					}
					
					
					// apply filters, if any
					$aParams = Geko_Hooks::applyFilter( sprintf( '%s::params', __METHOD__ ), $aParams, $sId, $sType, $oItem );
					
					// call script registry function/method
					call_user_func( $fCallback, $sId, $aParams );

					// printf( '%s - %s - %s<br />', $sId, $sFile, implode( ', ', $aDependencies ) );
					
				}
				
			}
		}
		
		return $this;
	}
	
	
	
	//
	public function debug() {
		
		print_r( $this->_aRegistered );
		echo "\n\n";
		print_r( $this->_aEnqueued );
	}
	
	
}



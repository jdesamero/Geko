<?php
/*
 * "geko_core/library/Geko/Bootstrap.php"
 * https://github.com/jdesamero/Geko
 *
 * Copyright (c) 2013 Joel Desamero.
 * Licensed under the MIT license.
 */

//
class Geko_Bootstrap extends Geko_Singleton_Abstract
{
	
	protected $_aRegistry = array();
	protected $_aRegAlias = array();
	
	
	// dependency tree for the various bootstrap components
	protected $_aDeps = array(
		'error' => NULL,
		'debug' => NULL,
		'logger' => NULL
	);
	
	protected $_aExtComponents = array();
	
	// config flags for desired modules
	protected $_aConfig = array(
		'error' => TRUE
	);
	
	// config values to be used, if any
	protected $_aValues = array(
		'live' => array(),
		'dev' => array(),
		// stage => array(),				// arbitrary optional conditional values
		// other => array(),
		'any' => array()
	);
	
	protected $_aValRules = NULL;
	
	/* /
	// sample
	protected $_aValRules = array(
		'live' => 'livedomain.com|www.livedomain.com',
		'stage' => 'stage.livedomain.com',
		'dev' => 'dev.geekoracle.com'
	);
	/* */
	
	
	protected $_aLoadedComponents = NULL;
	
	protected $_aPrefixes = array( 'Geko_' );
	protected $_sRootClass = NULL;
	
	protected $_aAbbrMap = array();
	
	
	
	
	
	//// static methods
	
	//
	public function set( $sKey, $mValue ) {
		
		$sResolvedKey = $this->resolveKey( $sKey );
		
		$this->_aRegistry[ $sResolvedKey ] = $mValue;
		
		return $this;
	}
	
	//
	public function get( $sKey ) {
		
		$sResolvedKey = $this->resolveKey( $sKey );
		
		return $this->_aRegistry[ $sResolvedKey ];
	}
	
	
	//
	public function setVal( $sKey, $mValue ) {
		return $this->set( sprintf( 'val:%s', $sKey ), $mValue );
	}
	
	//
	public function getVal( $sKey ) {
		return $this->get( sprintf( 'val:%s', $sKey ) );
	}
	
	
	
	// allow for resolution of both long and short forms
	public function resolveKey( $sKey ) {
		
		// only do this if $sKey does not begin with "val:"
		if ( 0 !== strpos( $sKey, 'val:' ) ) {
			
			// resolve key if it's not stored in $this->_aRegAlias
			if ( !$sResolvedKey = Geko_Array::getValue( $this->_aRegAlias, $sKey ) ) {

				$aResolved = array();
				$aKeyParts = explode( '.', $sKey );
				
				foreach ( $aKeyParts as $sPart ) {
					
					if ( !$sFull = Geko_Array::getValue( $this->_aAbbrMap, $sPart ) ) {
						$sFull = Geko_Inflector::camelize( strtolower( $sPart ) );
					}
					
					$aResolved[] = $sFull;
				}
				
				$sResolvedKey = implode( '_', $aResolved );			// the un-prefixed class name
				$this->_aRegAlias[ $sKey ] = $sResolvedKey;			// store
				
			}
			
			// return the resolved key
			return $sResolvedKey; 
		}
		
		return $sKey;
	}
	
	
	
	
	//// methods
	
	//
	protected function __construct() {
		
		parent::__construct();
		
		
		
		//// init best match static root class
		
		foreach ( $this->_aPrefixes as $sPrefix ) {
			
			$sClass = rtrim( $sPrefix, '_' );
			
			if ( class_exists( $sClass ) ) {
				
				call_user_func( array( $sClass, 'init' ), $this );
				$this->_sRootClass = $sClass;
				
				break;
			}
		}
		
		
		
		//// set values
		
		$aValues = $this->_aValues[ 'any' ];
		if ( !is_array( $aValues ) ) $aValues = array();
		
		
		// figure out live/dev/etc.
		
		if ( NULL !== $this->_aValRules ) {
			
			// do exact matching of $_SERVER[ 'SERVER_NAME' ] for now
			// we can add additional rules later if need be
			
			$sServerName = $_SERVER[ 'SERVER_NAME' ];
			
			foreach ( $this->_aValRules as $sRuleKey => $mRules ) {
				
				if ( is_string( $mRules ) ) {
					$aRules = Geko_Array::explodeTrim( '|', $mRules );
				} else {
					$aRules = $mRules;
				}
				
				if ( !is_array( $aRules ) ) {
					$aRules = array();
				}
				
				if ( in_array( $sServerName, $aRules ) ) {
					$aValues = array_merge( $aValues, $this->_aValues[ $sRuleKey ] );
					break;
				}
				
			}
			
		} else {
			
			if ( $this->isLiveServer() ) {
				if ( is_array( $this->_aValues[ 'live' ] ) ) {
					$aValues = array_merge( $aValues, $this->_aValues[ 'live' ] );
				}
			} else {
				if ( is_array( $this->_aValues[ 'dev' ] ) ) {
					$aValues = array_merge( $aValues, $this->_aValues[ 'dev' ] );
				}
			}
			
		}
		
		
		foreach ( $aValues as $sKey => $mVal ) {
			$this->setVal( $sKey, $mVal );
		}
		
		
		
		//// set reference to me
		
		$this->set( 'boot', $this );
		
	}

	
	
	
	//// functionality
	
	//
	public function start() {
		
		
		//
		parent::start();
		
		
		
		//// do it!!!
		
		$this->doInitPre();
		
		
		//// run the requested components
		
		$aConfig = $this->resolveConfig();
		
		$this->_aLoadedComponents = $aConfig;
		
		// $mArgs, use if needed later
		$i = 0;				// debug counter
		
		foreach ( $aConfig as $sComp => $mArgs ) {
			
			$this->loadComponent( $sComp, $mArgs, $i );
			$i++;
		}
		
		$this->doInitPost();
		
	}
	
	
	//
	public function loadComponent( $sComp, $mArgs, $i = NULL ) {
		
		$aArgs = Geko_Array::wrap( $mArgs );
		
		Geko_Debug::out( sprintf( '%d: %s', $i, $sComp ), sprintf( '%s::order', __METHOD__ ) );
		
		$sDebugMsg = '';
		
		if ( $fComponent = Geko_Array::getValue( $this->_aExtComponents, $sComp ) ) {
			
			// call external component first
			call_user_func( $fComponent, $aArgs );
			
			$sDebugMsg = 'Handled by external component';
			
		} else {
			
			// call internal, method-based component
			
			$aComp = $aCompFmt = explode( '.', $sComp );
			
			$aCompFmt = array_map( array( 'Geko_Inflector', 'camelize' ), $aCompFmt );
			$sCompFmt = implode( '_', $aCompFmt );
			
			$sMethod = sprintf( 'comp%s', $sCompFmt );
			
			// check first if method is defined
			if ( $bMethodExists = method_exists( $this, $sMethod ) ) {
				
				$this->$sMethod( $aArgs );
				$sDebugMsg = sprintf( 'Method %s() found', $sMethod );
				
			} else {
				
				// pass through "magic" handler
				if ( $this->handleComponent( $sComp, $aComp, $aArgs ) ) {
					$sDebugMsg = sprintf( 'Handled component %s', $sComp );
				} else {
					$sDebugMsg = sprintf( 'Unable to handle component %s', $sComp );
				}
				
			}
			
		}
		
		Geko_Debug::out( sprintf( '%s: %s', $this->_sInstanceClass, $sDebugMsg ), __METHOD__ );
		
	}
	
	
	
	// resolve any dependencies
	public function resolveConfig( $aConfig = NULL ) {
		
		if ( NULL === $aConfig ) {
			$aConfig = $this->_aConfig;
		}
		
		$aRes = array();
		
		foreach ( $aConfig as $sKey => $mArgs ) {
			if ( $mArgs ) {
				$aRes[ $sKey ] = $mArgs;
				$aRes = $this->getDeps( $aRes, $sKey );
			}
		}
		
		return $aRes;
	}
	
	// recursive function
	public function getDeps( $aConfig, $sKey ) {
		
		if ( is_array( $aDeps = Geko_Array::getValue( $this->_aDeps, $sKey ) ) ) {
			foreach ( $aDeps as $sDep ) {
				
				if ( !Geko_Array::getValue( $aConfig, $sDep ) ) {
					
					$aConfig = Geko_Array::insertBeforeKey( $aConfig, $sKey, $sDep, TRUE );
					$aConfig = $this->getDeps( $aConfig, $sDep );
					
				}
			}
		}
		
		return $aConfig;
	}
	
		
	
	
	
	//// accessors
	
	// configure components to be run
	public function config( $aParams = array() ) {
		
		$aParamsMerge = array();
		
		// force removal of unwanted default components
		foreach ( $aParams as $sKey => $mValue ) {
			if ( FALSE === $mValue ) {
				unset( $this->_aConfig[ $sKey ] );
			} else {
				$aParamsMerge[ $sKey ] = $mValue;
			}
		}
		
		$this->_aConfig = array_merge( $this->_aConfig, $aParamsMerge );
		
		// special
		
		if ( $mArgs = Geko_Array::getValue( $this->_aConfig, 'debug' ) ) {
			
			// run debug immediately
			$this->compDebug( $mArgs );
			unset( $this->_aConfig[ 'debug' ] );
		}
		
		return $this;
	}
	
	
	//
	public function getLoadedComponents() {
		return $this->_aLoadedComponents;
	}
	
	//
	public function registerComponent( $sKey, $fComponent = FALSE, $aDeps = NULL ) {
		
		$this->_aExtComponents[ $sKey ] = $fComponent;
		
		if ( is_array( $aDeps ) ) {
			$this->_aDeps[ $sKey ] = $aDeps;
		}
		
		return $this;
	}
	
	
	// to be used by sub-classes
	
	// add dependencies
	public function mergeDeps( $aDeps ) {
		
		$this->_aDeps = array_merge( $this->_aDeps, $aDeps );
		return $this;
	}
	
	// modify default config
	public function mergeConfig( $aConfig ) {
		
		$this->_aConfig = array_merge( $this->_aConfig, $aConfig );
		return $this;
	}
	
	// abbreviations
	public function mergeAbbrMap( $aAbbrMap ) {
		
		$this->_aAbbrMap = array_merge( $this->_aAbbrMap, $aAbbrMap );
		return $this;
	}
	
	
	
	// hook methods
	public function doInitPre() { }
	public function doInitPost() { }
	
	
	
	
	//// components
	
	
	// file path/url configuration
	// independent
	public function compPath( $aArgs ) {
		
		// register global urls to services
		
		if ( defined( 'GEKO_CORE_URI' ) ) {
			
			Geko_Uri::setUrl( array(
				'geko_export' => sprintf( '%s/srv/export.php', GEKO_CORE_URI ),
				'geko_gmap_overlay' => sprintf( '%s/srv/gmap_overlay.php', GEKO_CORE_URI ),
				'geko_pdf' => sprintf( '%s/srv/pdf.php', GEKO_CORE_URI ),
				'geko_process' => sprintf( '%s/srv/process.php', GEKO_CORE_URI ),
				'geko_scss' => sprintf( '%s/srv/scss.php', GEKO_CORE_URI ),
				'geko_thumb' => sprintf( '%s/srv/thumb.php', GEKO_CORE_URI ),
				'geko_upload' => sprintf( '%s/srv/upload.php', GEKO_CORE_URI ),
				'geko_styles' => sprintf( '%s/styles', GEKO_CORE_URI ),
				'geko_ext' => sprintf( '%s/external', GEKO_CORE_URI ),
				'geko_ext_images' => sprintf( '%s/external/images', GEKO_CORE_URI ),
				'geko_ext_styles' => sprintf( '%s/external/styles', GEKO_CORE_URI ),
				'geko_ext_swf' => sprintf( '%s/external/swf', GEKO_CORE_URI )
			) );
		
		}
		
	}

	
	
	
	// error handler/reporting
	public function compError( $aArgs ) {
		
		Geko_Error::start();
				
	}
	
	//
	public function compDebug( $aArgs ) {
		
		Geko_Debug::setShowOut( TRUE );
		
		if ( $mEnable = $aArgs[ 'enable' ] ) {
			
			$aEnable = Geko_Array::wrap( $mEnable );
			call_user_func_array( array( 'Geko_Debug', 'setOutEnable' ), $aEnable );	
		}
		
		if ( $mDisable = $aArgs[ 'disable' ] ) {
			
			$aDisable = Geko_Array::wrap( $mDisable );
			call_user_func_array( array( 'Geko_Debug', 'setOutDisable' ), $aDisable );	
		}
		
	}
	
	
	
	// know your geography!
	// independent
	public function compGeo( $aArgs ) {
		
		$oGeo = Geko_Geography_Xml::getInstance();
		
		if ( defined( 'GEKO_GEOGRAPHY_XML' ) ) {
			$oGeo->setFile( GEKO_GEOGRAPHY_XML );
		}
		
		$this->set( 'geo', $oGeo );
	}
	
	
	// know your money!
	// independent
	public function compCurrency( $aArgs ) {
		
		$oCurrency = Geko_Currency_Xml::getInstance();
		
		if ( defined( 'GEKO_CURRENCY_XML' ) ) {
			$oCurrency->setFile( GEKO_CURRENCY_XML );
		}
		
		$this->set( 'currency', $oCurrency );
	}
	
	
	
	
	// logger/debugger
	// independent
	public function compLogger( $aArgs ) {
		
		if ( $aArgs[ 0 ] ) {
			
			if ( is_int( $aArgs[ 0 ] ) ) {
				
				$oLogger = new Geko_Log( $aArgs[ 0 ], $aArgs[ 1 ] );
			
			} else {
	
				$aLoggerParams = array();
				
				if ( defined( 'GEKO_LOG_DISABLED' ) && GEKO_LOG_DISABLED ) {
					$iLoggerType = Geko_Log::WRITER_DISABLED;
				} elseif ( defined( 'GEKO_LOG_FIREBUG' ) && GEKO_LOG_FIREBUG ) {
					$iLoggerType = Geko_Log::WRITER_FIREBUG;
				} elseif ( defined( 'GEKO_LOG_STREAM' ) && GEKO_LOG_STREAM ) {
					$iLoggerType = Geko_Log::WRITER_STREAM;
				} else {
					if ( is_file( GEKO_LOG ) ) {
						$iLoggerType = Geko_Log::WRITER_FILE;
						$aLoggerParams[ 'file' ] = GEKO_LOG;
					} else {
						$iLoggerType = Geko_Log::WRITER_STREAM;
					}
				}
				
				$oLogger = new Geko_Log( $iLoggerType, $aLoggerParams );
			}
			
			// for backwards compatibility
			Zend_Registry::set( 'logger', $oLogger );
			
			$this->set( 'logger', $oLogger );
			
		}
	}
	
	
	
	//// component handler
	
	//
	public function handleComponent( $sKey, $aCompParts, $aArgs ) {
		
		if ( NULL === $aCompParts ) {
			$aCompParts = explode( '.', $sKey );
		}
		
		$aTrans = array();
		
		foreach ( $aCompParts as $sPart ) {
			
			if ( !( $sFull = $this->_aAbbrMap[ $sPart ] ) ) {
				$sFull = Geko_Inflector::camelize( strtolower( $sPart ) );
			}
			
			$aTrans[] = $sFull;
		}
		
		foreach ( $this->_aPrefixes as $sPrefix ) {
			
			$sClass = sprintf( '%s%s', $sPrefix, implode( '_', $aTrans ) );
			
			Geko_Debug::out( sprintf( 'Attempting to resolve class: %s', $sClass ), __METHOD__ );
			
			if ( class_exists( $sClass ) ) {
				
				Geko_Debug::out( sprintf( 'Class found: %s', $sClass ), __METHOD__ );
				
				$aBiArgs = array();			// Before init args
				$aAiArgs = array();			// After init args
				
				foreach ( $aArgs as $sArgKey => $aParams ) {
					
					if ( is_string( $sArgKey ) ) {
						
						if ( 0 === strpos( $sArgKey, 'ai:' ) ) {
							$aAiArgs[ substr( $sArgKey, 3 ) ] = $aParams;						
						} else {
							$aBiArgs[ $sArgKey ] = $aParams;
						}
					}
				}
				
				$oComp = Geko_Singleton_Abstract::getInstance( $sClass );
				
				if ( count( $aBiArgs ) > 0 ) {
					$oComp = $this->callComponentMethods( $oComp, $aBiArgs );			
				}
				
				if ( !$oComp->getCalledInit() ) {
					// call init() on singleton if not already called
					$oComp->init();
				}
				
				if ( count( $aAiArgs ) > 0 ) {
					$oComp = $this->callComponentMethods( $oComp, $aAiArgs );			
				}
				
				$this->set( $sKey, $oComp );
				
				return TRUE;
			}
		
		}
		
		return FALSE;
	}
	
	
	//
	public function callComponentMethods( $oComp, $aArgs ) {
		
		foreach ( $aArgs as $sKey => $aParams ) {
			
			$aParams = Geko_Array::wrap( $aParams );
			
			$sMethod = lcfirst( Geko_Inflector::camelize( $sKey ) );

			if ( !method_exists( $oComp, $sMethod ) ) {
				
				$sMethod = sprintf( 'set%s', ucfirst( $sMethod ) );
				
				if ( !method_exists( $oComp, $sMethod ) ) {
					// okay, give up
					$sMethod = NULL;
				}
			}
			
			if ( $sMethod ) {
				
				if ( count( $aParams ) > 0 ) {
					call_user_func_array( array( $oComp, $sMethod ), $aParams );
				} else {
					$oComp->$sMethod();
				}
			}
			
		}
		
		return $oComp;
	}
	
	
	
	
	
	
	//// run the bootstrap
	
	//
	public function run() {
		
		$this->doRunPre();
		$this->doRun();
		$this->doRunPost();
		
		Geko_Debug::out(
			sprintf( 'Loaded components: %s', implode( ', ', array_keys( $this->_aLoadedComponents ) ) ),
			__METHOD__
		);
		
		Geko_Debug::out(
			sprintf( 'Registered modules: %s', implode( ', ', array_keys( $this->_aRegistry ) ) ),
			__METHOD__
		);
		
		return $this;
	}
	
	// hook methods
	public function doRunPre() { }
	public function doRunPost() { }
	public function doRun() { }
	
	
	
	
	//// helpers
	
	//
	public function getBestMatch() {
		$aSuffixes = func_get_args();
		return Geko_Class::getBestMatch( $this->_aPrefixes, $aSuffixes );
	}
	
	//
	public function isLiveServer() {
		return FALSE;
	}
	
	
	
	
	
	//
	public function __call( $sMethod, $aArgs ) {
		
		if ( $sCreateType = Geko_Class::callCreateType( $sMethod ) ) {
			
			return Geko_Class::callCreateInstance( $sCreateType, $sMethod, $aArgs, $this->_aPrefixes );
			
		}
		
		throw new Exception( sprintf( 'Invalid method %s::%s() called.', __CLASS__, $sMethod ) );
	}
	
	
	
}





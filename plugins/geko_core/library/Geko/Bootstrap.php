<?php

//
class Geko_Bootstrap extends Geko_Singleton_Abstract
{
	
	protected $_aRegistry = array();
	
	
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
		'any' => array()
	);
	
	protected $_aLoadedComponents = NULL;
	
	protected $_aPrefixes = array( 'Geko_' );
	protected $_sRootClass = NULL;
	
	protected $_aAbbrMap = array();
	
	
	
	
	
	//// static methods
	
	//
	public function set( $sKey, $mValue ) {
		
		$this->_aRegistry[ $sKey ] = $mValue;
		
		return $this;
	}
	
	//
	public function get( $sKey ) {
		return $this->_aRegistry[ $sKey ];
	}
	
	
	//
	public function setVal( $sKey, $mValue ) {
		return $this->set( sprintf( 'val:%s', $sKey ), $mValue );
	}
	
	//
	public function getVal( $sKey ) {
		return $this->get( sprintf( 'val:%s', $sKey ) );
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
		
		if ( $this->isLiveServer() ) {
			if ( is_array( $this->_aValues[ 'live' ] ) ) {
				$aValues = array_merge( $aValues, $this->_aValues[ 'live' ] );
			}
		} else {
			if ( is_array( $this->_aValues[ 'dev' ] ) ) {
				$aValues = array_merge( $aValues, $this->_aValues[ 'dev' ] );
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
		$i = 0;
		
		
		foreach ( $aConfig as $sComp => $mArgs ) {
			
			$aArgs = Geko_Array::wrap( $mArgs );
			
			Geko_Debug::out( sprintf( '%d: %s', $i, $sComp ), sprintf( '%s::order', __METHOD__ ) );
			$i++;
			
			$sDebugMsg = '';
			
			if ( $fComponent = $this->_aExtComponents[ $sComp ] ) {
				
				// call external component first
				call_user_func( $fComponent, $aArgs );
				
				$sDebugMsg = 'Handled by external component';
				
			} else {
				
				// call internal, method-based component
				
				$aComp = $aCompFmt = explode( '.', $sComp );
				
				array_walk( $aCompFmt, array( 'Geko_Inflector', 'camelize' ) );
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
		
		$this->doInitPost();
		
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
		
		if ( is_array( $aDeps = $this->_aDeps[ $sKey ] ) ) {
			foreach ( $aDeps as $sDep ) {
				
				if ( !$aConfig[ $sDep ] ) {
					
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
		
		if ( $mArgs = $this->_aConfig[ 'debug' ] ) {
			
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
	
	// logger/debugger
	// independent
	public function compLogger( $aArgs ) {
		
		$oLogger = Zend_Registry::get( 'logger' );
		
		if ( !$oLogger ) {
			$oLogger = new Geko_Log( $aArgs[ 0 ], $aArgs[ 1 ] );
		}
		
		if ( $oLogger ) {
			$this->set( 'logger', $oLogger );
		}
	}
	
	
	
	//// component handler
	
	//
	public function handleComponent( $sKey, $aCompParts, $aArgs ) {
		
		$aTrans = array();
		
		foreach ( $aCompParts as $sPart ) {
			
			if ( !( $sFull = $this->_aAbbrMap[ $sPart ] ) ) {
				$sFull = ucfirst( strtolower( $sPart ) );
			}
			
			$aTrans[] = $sFull;
		}
		
		foreach ( $this->_aPrefixes as $sPrefix ) {
			
			$sClass = sprintf( '%s%s', $sPrefix, implode( '_', $aTrans ) );
			
			Geko_Debug::out( sprintf( 'Attempting to resolve class: %s', $sClass ), __METHOD__ );
			
			if ( class_exists( $sClass ) ) {
				
				Geko_Debug::out( sprintf( 'Class found: %s', $sClass ), __METHOD__ );
				
				$oSingleton = Geko_Singleton_Abstract::getInstance( $sClass );
				$oSingleton->init();
				
				$this->set( $sKey, $oSingleton );
				
				return TRUE;
			}
		
		}
		
		return FALSE;
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
	
	
	
	
}





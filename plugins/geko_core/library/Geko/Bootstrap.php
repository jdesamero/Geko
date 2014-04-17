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
	
	protected $_aLoadedComponents = NULL;
	
	protected $_aPrefixes = array( 'Geko_' );
	
	
	
	
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
		foreach ( $aConfig as $sComp => $mArgs ) {
			
			if ( $fComponent = $this->_aExtComponents[ $sComp ] ) {
				
				// call external component first
				call_user_func( $fComponent, $mArgs );
				
			} else {
				
				
				// call internal, method-based component
				
				$aComp = explode( '.', $sComp );
				array_walk( $aComp, array( 'Geko_Inflector', 'camelize' ) );
				$sComp = implode( '_', $aComp );
				
				$sMethod = sprintf( 'comp%s', $sComp );
				
				if ( $bMethodExists = method_exists( $this, $sMethod ) ) {
					$this->$sMethod( $mArgs );
				}
				
				
				
				// debugging
				
				$sMsg = ( $bMethodExists ) ? 'found' : 'NOT found' ;
				Geko_Debug::out( sprintf( '%s: Method %s() %s', get_class( $this ), $sMethod, $sMsg ), __METHOD__ );
				
			}
			
		}
		
		$this->doInitPost();
		
	}
		
	// resolve any dependencies
	public function resolveConfig( $aConfig = NULL ) {
		
		if ( NULL === $aConfig ) {
			$aConfig = $this->_aConfig;
		}
		
		foreach ( $aConfig as $sKey => $mArgs ) {
			if ( $mArgs ) {
				$aConfig[ $sKey ] = $mArgs;
				$aConfig = $this->getDeps( $aConfig, $sKey );
			}
		}
		
		return $aConfig;
	}
	
	// recursive function
	public function getDeps( $aConfig, $sKey ) {
		
		if ( $aDeps = $this->_aDeps[ $sKey ] ) {
			foreach ( $aDeps as $sDep ) {
				$aConfig = array_merge( array( $sDep => TRUE ), $aConfig );
				$aConfig = $this->getDeps( $aConfig, $sDep );
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
	
	
	
	// hook methods
	public function doInitPre() { }
	public function doInitPost() { }
	
	
	
	
	//// components
	
	// error handler/reporting
	public function compError( $mArgs ) {
		
		Geko_Error::start();
				
	}
	
	//
	public function compDebug( $mArgs ) {
		
		Geko_Debug::setShowOut( TRUE );

		if ( is_array( $mArgs ) ) {
			
			if ( $mEnable = $mArgs[ 'enable' ] ) {
				
				$aEnable = Geko_Array::wrap( $mEnable );
				call_user_func_array( array( 'Geko_Debug', 'setOutEnable' ), $aEnable );	
			}
			
			if ( $mDisable = $mArgs[ 'disable' ] ) {
				
				$aDisable = Geko_Array::wrap( $mDisable );
				call_user_func_array( array( 'Geko_Debug', 'setOutDisable' ), $aDisable );	
			}
			
			
		}
		
	}
	
	// logger/debugger
	// independent
	public function compLogger( $mArgs ) {
		
		$oLogger = Zend_Registry::get( 'logger' );
		
		if ( !$oLogger && is_array( $mArgs ) ) {
			$oLogger = new Geko_Log( $mArgs[ 0 ], $mArgs[ 1 ] );
		}
		
		if ( $oLogger ) {
			$this->set( 'logger', $oLogger );
		}
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
	
	
	
}





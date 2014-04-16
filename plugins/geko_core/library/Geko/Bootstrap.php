<?php

//
class Geko_Bootstrap extends Geko_Singleton_Abstract
{
	
	protected $_aRegistry = array();
	
	
	protected $_aDeps = array();					// dependency tree for the various app components
	
	protected $_aExtComponents = array();
	
	protected $_aConfig = array();					// config flags for desired modules
	
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
		
	//
	public function config( $aParams ) {
		$this->_aConfig = array_merge( $this->_aConfig, $aParams );
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

	
	// hook methods
	public function doInitPre() { }
	public function doInitPost() { }
	
	
	
	
	
	
	//// run the app
	
	//
	public function run() {
		
		$this->doRunPre();
		$this->doRun();
		$this->doRunPost();
		
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





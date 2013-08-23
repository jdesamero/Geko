<?php

//
class Geko_Router
{
	
	protected $_sBaseUrl;
	protected $_aPathItems = array();
	
	protected $_aRoutes = array();
	
	
	//
	public function __construct( $sBaseUrl ) {
		
		$this->_sBaseUrl = $sBaseUrl;
		
		$oUrl = Geko_Uri::getGlobal();
		
		// echo $oUrl->getHost() . '<br />';
		// echo $oUrl->getPath() . '<br />';
		
		$sPath = $oUrl->getPath();
		
		$sBaseUrlChop = str_replace( array( 'http://', 'https://' ), '', $sBaseUrl );
		
		$aRegs = array();
		if ( preg_match( '/\/.*/', $sBaseUrlChop, $aRegs ) ) {
			$sPrePath = $aRegs[ 0 ];
			if ( 0 === strpos( $sPath, $sPrePath ) ) {
				$sPath = substr( $sPath, strlen( $sPrePath ) );
			}
		}
		
		$this->_aPathItems = array_filter( explode( '/', $sPath ) );
		
		// print_r( $this->_aPathItems );
		
	}
	
	//// accessors
	
	//
	public function getPathItems() {
		return $this->_aPathItems;
	}
	
	//
	public function addRoute( $oRoute ) {
		$oRoute->setRouter( $this );
		$this->_aRoutes[] = $oRoute;
		return $this;
	}
	
	//
	public function prependRoute( $oRoute ) {
		$oRoute->setRouter( $this );
		array_unshift( $this->_aRoutes, $oRoute );
		return $this;
	}
	
	
	//// functionality
	
	//
	public function run() {
		
		foreach ( $this->_aRoutes as $oRoute ) {

			// echo 'Running... ' . get_class( $oRoute ) . '<br />';
			
			if ( $oRoute->isMatch() ) {
				$oRoute->run();
				break;
			}
			
		}
		
		return $this;
	}
	
	
}



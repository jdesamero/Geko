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
		
		$this->_aPathItems = Geko_Array::explodeTrimEmpty( '/', $sPath );
		
		// print_r( $this->_aPathItems );
		
	}
	
	//// accessors
	
	//
	public function getPathItems() {
		return $this->_aPathItems;
	}
	
	//
	public function addRoute( $oRoute, $iPriority = 1000, $sKey = NULL ) {
		
		static $i = 0;
		
		if ( !$sKey ) {
			$sKey = get_class( $oRoute );
		}
		
		$this->_aRoutes[ $sKey ] = array(
			'route' => $oRoute,
			'priority' => $iPriority,
			'idx' => $i++
		);
		
		$oRoute->setRouter( $this );
		
		return $this;
	}
	
	// ???
	public function prependRoute( $oRoute, $iPriority = 1000, $sKey = NULL ) {
		
		$this->addRoute( $oRoute, $iPriority, $sKey );
		
		return $this;
	}
	
	//
	public function removeRoute( $sKey ) {
		unset( $this->_aRoutes[ $sKey ] );
		return $this;
	}
	
	
	
	//// functionality
	
	//
	public function run() {
		
		// sort by priority before running
		uasort( $this->_aRoutes, array( $this, 'sortByPriority' ) );
				
		foreach ( $this->_aRoutes as $aRoute ) {
			
			$oRoute = $aRoute[ 'route' ];
			
			// echo 'Running... ' . get_class( $oRoute ) . '<br />';
			
			if ( $oRoute->isMatch() ) {
				$oRoute->run();
				break;
			}
			
		}
		
		return $this;
	}
	
	//
	public function sortByPriority( $a, $b ) {
		
		$a1 = $a[ 'priority' ];
		$b1 = $b[ 'priority' ];
		
		if ( $a1 == $b1 ) {

			$a2 = $a[ 'idx' ];
			$b2 = $b[ 'idx' ];
			
			if ( $a2 == $b2 ) return 0 ;
			return ( $a2 < $b2 ) ? -1 : 1 ;
		}
		
		return ( $a1 < $b1 ) ? -1 : 1 ;
	}	
	
	//
	public function debug() {
		
		// sort by priority before running
		uasort( $this->_aRoutes, array( $this, 'sortByPriority' ) );
		
		foreach ( $this->_aRoutes as $sKey => $aRoute ) {
			
			$oRoute = $aRoute[ 'route' ];
			$iPriority = $aRoute[ 'priority' ];
			$iIdx = $aRoute[ 'idx' ];
			
			printf( '%s - %s - %d<br />', $sKey, $iPriority, $iIdx );
		}
			
	}
	
	
}



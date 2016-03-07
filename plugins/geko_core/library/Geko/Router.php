<?php
/*
 * "geko_core/library/Geko/Router.php"
 * https://github.com/jdesamero/Geko
 *
 * Copyright (c) 2013 Joel Desamero.
 * Licensed under the MIT license.
 */

//
class Geko_Router
{
	
	protected $_oPath = NULL;
	
	protected $_aRoutes = array();
	protected $_oCurrentRoute = NULL;
	
	protected $_aTokens = array();
	
	protected $_bStopRunning = FALSE;
	
	
	
	//
	public function __construct( $sBaseUrl ) {
		
		$oPath = new Geko_Router_Path( $sBaseUrl );
		
		$this->_oPath = $oPath;
		
	}
	
	
	
	
	
	//// accessors
	
	//
	public function getPath() {
		
		return $this->_oPath;
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
		
		$oRoute->setRouter( $this, $sKey );
		
		return $this;
	}
	
	//
	public function setCurrentRoute( $oRoute ) {
		
		$oPath = $this->_oPath;
		
		$this->_oCurrentRoute = $oRoute;
		
		$oPath->setTarget( $oRoute->getTarget() );
		$oPath->setRouteName( $oRoute->getRouteName() );
		
	}
	
	
	//
	public function removeRoute( $sKey ) {
		
		unset( $this->_aRoutes[ $sKey ] );
		
		return $this;
	}
	
	//
	public function setStopRunning( $bStopRunning ) {
		
		$this->_bStopRunning = $bStopRunning;
		
		return $this;
	}
	
	//
	public function stopRunning() {
		
		return $this->_bStopRunning;
	}
	
	// messaging tokens
	public function setToken( $sKey, $mValue ) {
		
		$this->_aTokens[ $sKey ] = $mValue;
		
		return $this;
	}
	
	//
	public function getToken( $sKey ) {
		
		return $this->_aTokens[ $sKey ];
	}
	
	//
	public function hasToken( $sKey ) {
		
		return array_key_exists( $sKey, $this->_aTokens ) ? TRUE : FALSE ;
	}
	
	
	
	
	
	//// functionality
	
	//
	public function run() {
		
		// sort by priority before running
		uasort( $this->_aRoutes, array( $this, 'sortByPriority' ) );
		
		foreach ( $this->_aRoutes as $aRoute ) {
			
			$oRoute = $aRoute[ 'route' ];
			
			Geko_Debug::out( sprintf( 'Running... %s', get_class( $oRoute ) ), __METHOD__ );
			
			if ( $oRoute->isMatch() ) {
				
				$oRoute->run();
				
				if ( $this->stopRunning() ) break;
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
			
			Geko_Debug::out( sprintf( '%s - %s - %d', $sKey, $iPriority, $iIdx ), __METHOD__ );
		}
			
	}
	
	
}



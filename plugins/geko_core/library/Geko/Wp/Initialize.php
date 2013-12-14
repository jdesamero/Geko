<?php

//
class Geko_Wp_Initialize extends Geko_Singleton_Abstract
{
	//
	private static $aCalledCoft = array();
	private static $aCalledCo = array();
		
	protected $_sInstanceClass;
	
	
	//
	protected function __construct() {
		parent::__construct();
		$this->_sInstanceClass = get_class( $this );
	}
	
	
	// calling init() ensures add() is only called once while in admin mode
	public function init() {
		
		$this->coft_affix();
		$this->co_add();
		
		if ( !is_admin() ) {
			$this->coft_affixTheme();
			$this->co_addTheme();
			
			// TO DO: need trigger method for enqueue
			// if ( $this->someHook() ) $this->co_enqueueTheme();
		}
		
		if ( is_admin() ) {
			$this->coft_affixAdmin();
			$this->co_addAdmin();
			if ( $this->isCurrentPage() ) $this->co_enqueueAdmin();
		}
		
		return $this;
	}
	
	
	
	//
	public function isCurrentPage() {
		return FALSE;
	}

	
	
	//// CO (call once) methods
	
	//
	public function add() {
		return $this;
	}
	
	//
	public function addTheme() {
		return $this;
	}
	
	//
	public function addAdmin() {
		return $this;	
	}
	
	//
	public function addAdminHead() {
		return $this;	
	}
	
	
	
	//
	public function enqueueAdmin() {
		return $this;
	}
	
	//
	public function enqueueTheme() {
		return $this;
	}
	
	
	
	
	
	
	
	//// COFT (call once from top) methods
	
	//
	public function affix() {
		return $this;
	}
	
	//
	public function affixTheme() {
		return $this;
	}
	
	//
	public function affixAdmin() {
		return $this;	
	}
	
	//
	public function affixAdminHead() {
		return $this;	
	}
	
	//
	public function install() {
		return $this;
	}
	
	
	
	
	//// magic methods
	
	//
	public function __call( $sMethod, $aArgs ) {
		
		$sClassName = get_class( $this );
		
		if (
			( 0 === strpos( $sMethod, 'coft_' ) ) && 
			( method_exists( $this, ( $sRealMethod = substr_replace( $sMethod, '', 0, 5 ) ) ) )
		) {
			
			// COFT: call once from top (meaning, start at the top of the hierarchy first)
			
			// create class hierarchy
			$aHier = array();
			$aHier[] = $sClassName;
			
			while ( $sClassName = get_parent_class( $sClassName ) ) {
				if (
					( method_exists( $sClassName, $sRealMethod ) ) && 
					( !isset( self::$aCalledCoft[ $sClassName . '::' . $sRealMethod ] ) )
				) {
					array_unshift( $aHier, $sClassName );
				} else {
					break;
				}
			}
			
			// make calls
			foreach ( $aHier as $sClassName ) {
				$sMethodSig = $sClassName . '::' . $sRealMethod;
				if ( !isset( self::$aCalledCoft[ $sMethodSig ] ) ) {
					
					// check if the class declares the method, if not don't call
					// anything since it will invoke the parent method
					
					$oRc = new ReflectionClass( $sClassName );
					$oRm = $oRc->getMethod( $sRealMethod );
					if ( $oRm->getDeclaringClass()->getName() == $sClassName ) {
						$oCall = Geko_Singleton_Abstract::getInstance( $sClassName );
						call_user_func_array( array( $oCall, $sRealMethod ), $aArgs );					
					}
					
					// echo 'calling: ' . $sMethodSig . "\n";
					self::$aCalledCoft[ $sMethodSig ] = TRUE;
				} else {
					// echo 'already called: ' . $sMethodSig . "\n";				
				}
			}
			
			// print_r( self::$aCalledCoft );
			// print_r( $aHier );
			
			return $this;				
		
		} elseif (
			( 0 === strpos( $sMethod, 'co_' ) ) && 
			( method_exists( $this, ( $sRealMethod = substr_replace( $sMethod, '', 0, 3 ) ) ) )
		) {
			
			// CO: call once
			
			$sMethodSig = $sClassName . '::' . $sRealMethod;				
			
			if ( !isset( self::$aCalledCoft[ $sMethodSig ] ) ) {
				
				self::$aCalledCoft[ $sMethodSig ] = TRUE;
				
				$oCall = Geko_Singleton_Abstract::getInstance( $sClassName );
				return call_user_func_array( array( $oCall, $sRealMethod ), $aArgs );
			}
			
			return $this;
			
		} elseif (
			( 0 === strpos( $sMethod, 'getCalled_' ) ) && 
			( method_exists( $this, ( $sRealMethod = substr_replace( $sMethod, '', 0, 10 ) ) ) )
		) {
			
			// test if COFT or CO was called for the method
			$sMethodSig = $sClassName . '::' . $sRealMethod;
			return ( self::$aCalledCoft[ $sMethodSig ] || self::$aCalledCo[ $sMethodSig ] );
		
		} elseif ( 0 === strpos( $sMethod, 'get' ) ) {
			
			// return results of an echo method as a string if there is a corresponding method
			$sCall = substr_replace( $sMethod, 'echo', 0, 3 );
			
			if ( method_exists( $this, $sCall ) ) {
				ob_start();
				call_user_func_array( array( $this, $sCall ), $aArgs );
				$s = ob_get_contents();
				ob_end_clean();
				return $s;
			}
		}
		
		throw new Exception('Invalid method ' . get_class( $this ) . '::' . $sMethod . '() called.');
	}
	
	
}



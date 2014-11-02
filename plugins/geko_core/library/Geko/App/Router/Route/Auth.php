<?php

//
class Geko_App_Router_Route_Auth extends Geko_Router_Route
{
	
	protected $_oAuth;
	
	protected $_aRules = array();
	
	
	
	//
	public function __construct( $oAuth = NULL ) {
		
		if ( NULL === $oAuth ) {
			$oAuth = Geko_App::get( 'auth' );
		}
		
		$this->_oAuth = $oAuth;
	}
	
	
	//
	public function addRule( $sRule ) {
		$this->_aRules[] = $sRule;
		return $this;
	}
	
	//
	public function addRules( $aRules ) {
		
		$this->_aRules = array_merge(
			$this->_aRules,
			Geko_Array::wrap( $aRules )
		);
		
		return $this;
	}
	
	//
	public function clearRules() {
		$this->_aRules = array();
		return $this;
	}
	
	
	//
	public function isMatch() {
		
		$oRouter = $this->_oRouter;
		
		$aPathItems = $oRouter->getPathItems();
		
		$bAllow = FALSE;

		// Rule formatting:
		//
		// allow: *						// allow everything
		// deny: *						// deny everything, default
		// allow: /contact/*			// allow this and sub-items
		// deny: /srv/this/that/*		// deny this and sub-items
		// allow: /contact				// exactly allow this
		// allow: /contact				// exactly deny this
		
		$iPathItemsCount = count( $aPathItems );
		
		// iterate until *ALL* rules are exhausted
		foreach ( $this->_aRules as $sRule ) {
			
			$aParts = explode( ':', $sRule, 2 );
			
			$sMode = trim( $aParts[ 0 ] );
			$sRulePath = trim( $aParts[ 1 ] );
			
			$aRulePath = Geko_Array::explodeTrimEmpty( '/', $sRulePath );
			
			// see if the rule path applies to the path items
			$bRuleApplies = FALSE;
			
			$iRulePathCount = count( $aRulePath );
			
			// go through each rule
			foreach ( $aRulePath as $i => $sItem ) {
				
				// wildcard match
				if ( '*' == $sItem ) {
					$bRuleApplies = TRUE;
					break;
				}
				
				// exact match
				if ( $aPathItems[ $i ] == $sItem ) {
					if (
						( $iRulePathCount == ( $i + 1 ) ) && 
						( $iRulePathCount == $iPathItemsCount )
					) {
						$bRuleApplies = TRUE;
					}
					continue;
				}
				
				// no match
				if ( $aPathItems[ $i ] != $sItem ) {
					$bRuleApplies = FALSE;
					break;
				}
			}
			
			if ( $bRuleApplies ) {
				$bAllow = ( 'allow' == $sMode ) ? TRUE : FALSE ;
			}
			
		}
		
		
		// public page, allow
		if ( $bAllow ) return FALSE;
		
		// no auth object, deny
		if ( !$oAuth = $this->_oAuth ) return TRUE;
		
		// not logged-in, deny
		if ( !$oAuth->hasIdentity() ) return TRUE;
		
		// allow
		return FALSE;
		
	}
	
	
	//
	public function run() {
		
		$oRouter = $this->_oRouter;
		
		// set AUTH_REQUIRED token
		$oRouter->setToken( 'AUTH_REQUIRED', TRUE );
		
	}
	
	
	
}
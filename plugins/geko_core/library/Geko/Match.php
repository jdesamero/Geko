<?php
/*
 * "geko_core/library/Geko/Match.php"
 * https://github.com/jdesamero/Geko
 *
 * Copyright (c) 2013 Joel Desamero.
 * Licensed under the MIT license.
 */

//
class Geko_Match
{
	
	protected static $oMatch;
	
	protected $_aRules = array();
	
	
	//
	public static function set( $oMatch ) {
		self::$oMatch = $oMatch;
	}
	
	//
	public static function is( $mParams ) {
		if ( self::$oMatch ) {
			return self::$oMatch->_is( $mParams );
		}
		return FALSE;
	}
	
	
	
	//
	public function __construct() {
	
	}
	
	
	//
	public function addRule( $oRule, $sKey = NULL ) {
		
		if ( NULL === $sKey ) {
			// use the rule's name if none was provided
			$sKey = $oRule->getRuleName();
		}
		
		$this->_aRules[ $sKey ] = $oRule;
		
		return $this;
	}
	
	// instance method
	public function _is( $mParams ) {
		
		// format:
		// cond:val1,val2|other:some,other
		
		if ( is_string( $mParams ) ) {
			$aParams = Geko_Array::explodeTrimEmpty( '|', $mParams );
			foreach ( $aParams as $i => $sSubParam ) {
				$aSubParam = Geko_Array::explodeTrimEmpty( ':', $sSubParam );
				if ( $aSubParam[ 1 ] ) {
					$aSubParam[ 1 ] = Geko_Array::explodeTrimEmpty( ',', $aSubParam[ 1 ] );
				}
				$aParams[ $i ] = $aSubParam;
			}
		} else {
			$aParams = $mParams;
		}
		
		foreach ( $aParams as $aSubParam ) {
			
			$sRule = $aSubParam[ 0 ];
			$aRuleParams = $aSubParam[ 1 ];
			
			if (
				( $oRule = $this->_aRules[ $sRule ] ) && 
				( $oRule->isMatch( $aRuleParams, $sRule ) )
			) {
				return TRUE;
			}
		}
		
		return FALSE;
	}

	
}


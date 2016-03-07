<?php

//
class Geko_Match_Rule_Route extends Geko_Match_Rule
{
	
	protected $_sRuleName = 'route';
	protected $_oRouter;
	
	
	
	//
	public function __construct( $oRouter ) {
		
		$this->_oRouter = $oRouter;
	}
	
	
	//
	public function isMatch( $aRuleParams, $sRule ) {
		
		$oRouter = $this->_oRouter;
		
		foreach ( $aRuleParams as $sRoute ) {
			
			$aMatch = Geko_Array::explodeTrimEmpty( '/', $sRoute );
			$aPathItems = $oRouter->getPathItems();
			
			if ( in_array( '*', $aMatch ) ) {
				
				// has wildcard
				foreach ( $aMatch as $i => $sItem ) {
					
					if ( $sItem != $aPathItems[ $i ] ) {
						
						if ( '*' == $sItem ) {
							
							// found wildcard
							return TRUE;
						
						} else {
							
							break;
						}
						
					}
					
				}
				
			} else {
				
				if ( $aMatch === $aPathItems ) return TRUE;
			}
			
		}
		
		return FALSE;
	}
	
}



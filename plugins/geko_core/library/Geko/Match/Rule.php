<?php

//
class Geko_Match_Rule
{
	
	protected $_sRuleName = '';
	
	
	//
	public function isMatch( $aRuleParams, $sRule ) {
		return FALSE;
	}
	
	//
	public function getRuleName() {
		return $this->_sRuleName;
	}
	
	
}




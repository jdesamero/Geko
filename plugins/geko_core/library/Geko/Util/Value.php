<?php

// "wrap" a value so it can be passed around with "other" info
class Geko_Util_Value
{
	
	protected $_aValues = array();
	
	//
	public function __construct() {
		
		$this->_aValues = func_get_args();
		
	}
	
	//
	public function get( $iIdx = 0 ) {
		
		return $this->_aValues[ $iIdx ];
	}
	
}




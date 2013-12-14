<?php

//
class Geko_Wp_Resolver_Path
{
	
	protected $_aPrefixes = array();
	
	//
	public function getPrefixes() {
		return $this->_aPrefixes;
	}
	
	//
	public function isMatch() {
		return FALSE;
	}

	//
	public function resolvePath( $sClassFile ) {
		return $sClassFile;
	}
	
}



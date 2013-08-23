<?php

//
class Geko_Router_Route
{

	protected $_oRouter;
	
	
	//
	public function setRouter( $oRouter ) {
		$this->_oRouter = $oRouter;
		return $this;
	}
	
	//
	public function isMatch() {
		return FALSE;
	}
	
	//
	public function run() { }
	
	
	
}



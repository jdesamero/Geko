<?php

//
class Geko_Router_Route
{
	
	protected $_aPrefixes = array( 'Tmpl_', 'Gloc_', 'Geko_' );
	
	protected $_oRouter;
	
	
	//
	public function setRouter( $oRouter ) {
		$this->_oRouter = $oRouter;
		return $this;
	}
	
	// implement by sub-class
	public function isMatch() {
		return FALSE;
	}
	
	//
	public function run() { }
	
	
	
	//// helpers

	//
	public function getBestMatch() {
		
		$aSuffixes = func_get_args();
		
		$aClasses = array();
		
		foreach ( $aSuffixes as $sSuffix ) {
			foreach ( $this->_aPrefixes as $sPrefix ) {
				$aClasses[] = $sPrefix . $sSuffix;
			}
		}
		
		return Geko_Class::existsCoalesce( $aClasses );
	}

	
}



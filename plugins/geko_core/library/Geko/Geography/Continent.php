<?php

//
class Geko_Geography_Continent extends Geko_Singleton_Abstract
{
	
	protected $_aContinents = NULL;
	
	
	
	//
	public function get() {
		
		if ( NULL === $this->_aContinents ) {
			$oGeo = Geko_Geography_Xml::getInstance();
			$oGeo->loadData( GEKO_GEOGRAPHY_XML );
		}
		
		return $this->_aContinents;
	}
	
	//
	public function set( $aContinents ) {
		$this->_aContinents = $aContinents;
	}
	
	// $sState could be code or name
	public function getNameFromCode( $sCode ) {
		
		$this->get();		// init $this->_aContinents
		
		return $this->_aContinents[ $sCode ];
	}
	
	
}



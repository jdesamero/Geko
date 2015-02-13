<?php

// pseudo type for html plain text
class Geko_Html_Element_Text extends Geko_Html_Element
{
	
	protected $_sElem = '_text';
	protected $_sContent = '';
	
	// standard
	protected $_aGlobalAtts = array();
	
	// html 4 only
	protected $_aGlobalAtts4 = array();
	
	// html 5 only
	protected $_aGlobalAtts5 = array();
		
	// standard
	protected $_aValidAtts = array();
	
	// html 4 only
	protected $_aValidAtts4 = array();
	
	// html 5 only
	protected $_aValidAtts5 = array();
	
	
	//
	public function _setContent( $sContent ) {
		
		if ( !is_string( $sContent ) ) {
			$sContent = strval( $sContent );
		}
		
		$this->_sContent = $sContent;
		
		return $this;
	}
	
	// override default method
	public function __toString() {
		
		if ( !$this->_sContent ) {
			return '';
		}
		
		return $this->_sContent;
	}
	
}


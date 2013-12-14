<?php

// a composite tree structure utilized by IndentedList
abstract class Geko_Composite_Abstract implements Geko_Composite_Interface
{
	protected $_aChildren;
	protected $_oParent;
	
	//// setters

	public function setParent($oParent) {
		$this->_oParent = $oParent;
		return $this;
	}
	
	public function setChild($aChild) {
		
		if ( FALSE == is_array($this->_aChildren) ) $this->_aChildren = array();
		
		$this->_aChildren[] = $aChild;
		return $this;
	}
	
	
	//// getters

	public function hasChildren() {
		if ( is_array($this->_aChildren) ) {
			return (count($this->_aChildren) > 0);
		} else {
			return FALSE;
		}
	}
	
	public function getChildren() {
		return $this->_aChildren;
	}

	public function getParent() {
		return $this->_oParent;
	}
	
	
	//// hooks
	
	public function setParams($oParams) { }
	public function setUp() { }
	
}


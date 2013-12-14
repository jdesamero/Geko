<?php

abstract class Geko_Navigation_Page_ImplicitLabelAbstract
	extends Geko_Navigation_Page
{
    
    //// object methods
    	
	//
	public function getLabel()
	{
		if ( $this->_label ) {
			return $this->_label;
		} else {
			return $this->getImplicitLabel();
		}
	}
	
	//
	abstract public function getImplicitLabel();
	
}


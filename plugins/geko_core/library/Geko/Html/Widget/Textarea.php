<?php

class Geko_Html_Widget_Textarea extends Geko_Html_Widget
{
	
	//
	public function get() {
		
		$aAtts = $this->_aAtts;
		$mValue = $this->_mValue;
		$aParams = $this->_aParams;
		
		return _ge( 'textarea', $aAtts, $mValue );
	}
	
	
}



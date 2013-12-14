<?php

class Geko_Html_Widget_Hidden extends Geko_Html_Widget
{
	
	//
	public function get() {
		
		$aAtts = $this->_aAtts;
		$mValue = $this->_mValue;
		$aParams = $this->_aParams;
		
		$aAtts = array_merge( $aAtts, array(
			'type' => 'hidden',
			'value' => $mValue
		) );
		
		$oInput = _ge( 'input', $aAtts );
		$oInput->addClass( 'geko-form-hidden' );
		
		return $oInput;
	}
	
	
}



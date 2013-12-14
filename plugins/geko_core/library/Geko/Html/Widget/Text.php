<?php

class Geko_Html_Widget_Text extends Geko_Html_Widget
{
	
	//
	public function get() {
		
		$aAtts = $this->_aAtts;
		$mValue = $this->_mValue;
		$aParams = $this->_aParams;
		
		$aAtts = array_merge( $aAtts, array(
			'type' => 'text',
			'value' => $mValue
		) );
		
		$oInput = _ge( 'input', $aAtts );
		$oInput->addClass( 'geko-form-text' );
		
		return $oInput;
		
	}
	
	
}



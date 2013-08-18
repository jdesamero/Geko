<?php

class Geko_Html_Widget_Checkbox extends Geko_Html_Widget
{
	
	//
	public function get() {
		
		$aAtts = $this->_aAtts;
		$mValue = $this->_mValue;
		$aParams = $this->_aParams;
		
		$aAtts = array_merge( $aAtts, array(
			'type' => 'checkbox'
		) );
		
		if ( $mValue ) $aAtts[ 'checked' ] = 'checked';
		
		return _ge( 'input', $aAtts );
		
	}
	
	
}



<?php

class Geko_Html_Widget_SelectMulti extends Geko_Html_Widget
{
	
	//
	public function get() {
		
		$aAtts = $this->_aAtts;
		$mValue = $this->_mValue;
		$aParams = $this->_aParams;
		
		$aAtts = array_merge( $aAtts, array(
			'multiple' => 'multiple'
		) );
		
		$oSelect = _ge( 'select', $aAtts );
		
		$aChoices = ( is_array( $aParams[ 'choices' ] ) ) ? $aParams[ 'choices' ] : array();
		
		foreach ( $aChoices as $mOptValue => $mOptParams ) {

			$aOptAtts = array( 'value' => $mOptValue );
			
			$sLabel = is_string( $mOptParams ) ? $mOptParams : '' ;
			
			if ( is_array( $mOptParams ) ) {
				$sLabel = $mOptParams[ 'label' ];
				if ( is_array( $mOptParams[ 'atts' ] ) ) {
					$aOptAtts = array_merge( $aOptAtts, $mOptParams[ 'atts' ] );
				}
			}
			
			if ( in_array( $mOptValue, $mValue ) ) $aOptAtts[ 'selected' ] = 'selected';
			$oSelect->append( _ge( 'option', $aOptAtts, $sLabel ) );
			
		}
		
		return $oSelect;

	}
	
	
}



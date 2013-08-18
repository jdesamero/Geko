<?php

class Geko_Html_Widget_CheckboxMulti extends Geko_Html_Widget
{
	
	//
	public function get() {
		
		$aAtts = $this->_aAtts;
		$mValue = $this->_mValue;
		$aParams = $this->_aParams;
		
		$oDiv = _ge( 'div' );
		
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
			
			if ( in_array( $mOptValue, $mValue ) ) $aOptAtts[ 'checked' ] = 'checked';
			
			$aOptAtts = array_merge( $aOptAtts, array(
				'type' => 'checkbox'
			) );
			
			$oInput = _ge( 'input', $aOptAtts );
			
			$oDiv
				->append( $oInput )
				->append( '&nbsp;&nbsp;&nbsp;' )
				->append( _ge( 'label' )->append( $sLabel ) )
				->append( _ge( 'br' ) )
			;
			
		}
		
		return $oDiv;
	}
	
	
}



<?php

class Geko_Html_Widget_Select extends Geko_Html_Widget
{
	
	//
	public function get() {
		
		$aAtts = $this->_aAtts;
		$mValue = $this->_mValue;
		$aParams = $this->_aParams;
		
		$oSelect = _ge( 'select', $aAtts );
		
		if ( $mEmptyChoice = $aParams[ 'empty_choice' ] ) {
			
			$aOptAtts = array( 'value' => '' );

			$sLabel = is_string( $mEmptyChoice ) ? $mEmptyChoice : '' ;
			
			if ( is_array( $mEmptyChoice ) ) {
				$sLabel = $mEmptyChoice[ 'label' ];
				if ( is_array( $mEmptyChoice[ 'atts' ] ) ) {
					$aOptAtts = array_merge( $aOptAtts, $mEmptyChoice[ 'atts' ] );
				}
			}
			
			$oSelect->append( _ge( 'option', $aOptAtts, $sLabel ) );		
		}
		
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
			
			if ( $mOptValue == $mValue ) $aOptAtts[ 'selected' ] = 'selected';
			$oSelect->append( _ge( 'option', $aOptAtts, $sLabel ) );
			
		}
		
		return $oSelect;
	}
	
	
}



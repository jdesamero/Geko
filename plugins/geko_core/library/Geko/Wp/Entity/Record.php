<?php

// this is a Geko_Delegate
class Geko_Wp_Entity_Record extends Geko_Entity_Record
{
	
	//
	public function formatWpErrorMessages( $oWpError ) {
		
		return implode( '; ', $oWpError->get_error_messages() );
	}
	
	
	// wordpress likes to save "true" and "false" literals as meta values
	public function formatTrueFalse( $aValues, $aKeys ) {
		
		foreach ( $aKeys as $sKey ) {
			
			if ( array_key_exists( $sKey, $aValues ) ) {
				
				$mVal = $aValues[ $sKey ];
				
				if ( is_string( $mVal ) ) $mVal = strtolower( $mVal );
				
				if ( ( 'true' !== $mVal ) && ( 'false' !== $mVal ) ) {
					$mVal = ( $mVal ) ? 'true' : 'false' ;
				}
				
				$aValues[ $sKey ] = $mVal;
			}
		
		}
		
		return $aValues;
	}
	
	
}


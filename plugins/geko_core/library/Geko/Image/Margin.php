<?php

//
class Geko_Image_Margin
{

	// generate a margin array from a string
	public static function getArray( $mMargin ) {
		
		// already an array
		if ( is_array( $mMargin ) ) return $mMargin;
		
		// treat $mMargin as string
		$aMargin = explode( '|', $mMargin );
		
		if ( count( $aMargin ) == 1 ) {
			
			$i = $aMargin[ 0 ];
			$aMargin = array( $i, $i, $i, $i );
		
		} elseif ( count( $aMargin ) == 2 ) {
			
			$i = $aMargin[ 0 ];
			$j = $aMargin[ 1 ];
			$aMargin = array( $i, $j, $i, $j );
		
		} elseif ( count( $aMargin ) == 4 ) {
			
			// do nothing
		
		} else {
			
			$aMargin = array( 0, 0, 0, 0 );
		}
		
		return $aMargin;
	}
	
	//
	public static function getString( $mMargin ) {
		
		// not an array
		if ( !is_array( $mMargin ) ) return $mMargin;
		
		// treat $mMargin as an array
		return implode( '|', $mMargin );
	}
	

}


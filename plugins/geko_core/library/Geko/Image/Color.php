<?php

//
class Geko_Image_Color
{

	// generate an rgb color array from a string
	public static function getArray( $mHex, $sDef = 'fff' ) {
		
		// already an array
		if ( is_array( $mHex ) ) return $mHex;
		
		// treat $mHex as string
		if ( ( 3 != strlen( $mHex ) ) && ( 6 != strlen( $mHex ) ) ) {
			$mHex = $sDef;
		}
		
		$aColor = array();
		
		if ( 3 == strlen( $mHex ) ) {
			
			// 3 chars
			$aColor[ 'r' ] = hexdec( str_repeat( substr( $mHex, 0, 1 ), 2 ) );
			$aColor[ 'g' ] = hexdec( str_repeat( substr( $mHex, 1, 1 ), 2 ) );
			$aColor[ 'b' ] = hexdec( str_repeat( substr( $mHex, 2, 1 ), 2 ) );
		
		} else {
			
			// 6 chars
			$aColor[ 'r' ] = hexdec( substr( $mHex, 0, 2 ) );
			$aColor[ 'g' ] = hexdec( substr( $mHex, 2, 2 ) );
			$aColor[ 'b' ] = hexdec( substr( $mHex, 4, 2 ) );		
		}
		
		return $aColor;
	}
	
	
	//
	public static function getString( $mHex, $sDef = 'fff' ) {
		
		// not an array
		if ( !is_array( $mHex ) ) return $mHex;
		
		// treat $mHex as array
		$sColor = '';
		
		foreach ( $mHex as $sHex ) {
			if ( 1 == strlen( $sHex ) ) {
				$sHex = str_repeat( dechex( $sHex ), 2 );
			}
			$sColor .= $sHex;
		}
		
		if ( !$sColor ) $sColor = $sDef;
		
		return $sColor;
	}
	
	//
	public static function allocate( $rSource, $mHex ) {
		
		$aColor = self::getArray( $mHex );
		return imagecolorallocate( $rSource, $aColor[ 'r' ], $aColor[ 'g' ], $aColor[ 'b' ] );
		
	}
	
}



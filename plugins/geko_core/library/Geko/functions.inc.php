<?php

// wordpress specific
if ( defined( 'ABSPATH' ) ) {
	
	// constants
	define( 'GEKO_WP_ABSPATH', substr_replace( ABSPATH, '', strlen( ABSPATH ) - 1, strlen( ABSPATH ) ) );
	
}

// adds support for newer native functions in PHP

if ( !function_exists( 'get_called_class' ) ) {
	
	// native function available to PHP 5 >= 5.3.0 only
	// http://ca.php.net/manual/en/function.get-called-class.php
	
	// implementation by progman at centrum dot sk
	// When you use this function in singleton, mind there can by only one getInstance() call per line
	// eg: $s1 = MySingleton1::getInstance(); $s2 = MySingleton2::getInstance();
	// above can be ambiguous
	
	function get_called_class() {
		
		$aBt = debug_backtrace();
		// print_r( $aBt );
		
		$iTraceCount = count( $aBt );
		
		for ( $i = 1; $i < $iTraceCount; $i++ ) {
			
			if ( is_file( $aBt[ $i ][ 'file' ] ) ) {
				
				$aLines = file( $aBt[ $i ][ 'file' ] );
				
				preg_match(
					'/([a-zA-Z0-9\_]+)::' . $aBt[ $i ][ 'function' ] .'/',
					$aLines[ $aBt[ $i ][ 'line' ] - 1 ],
					$aMatches
				);
				
				if (
					( 'self' != $aMatches[ 1 ] ) && 
					( 'parent' != $aMatches[ 1 ] ) && 
					( '' != $aMatches[ 1 ] )
				) {
					return $aMatches[ 1 ];
				}
							
				// handles call_user_func() and call_user_func_array()
				if (
					( 'call_user_func' == $aBt[ $i ][ 'function' ] ) || 
					( 'call_user_func_array' == $aBt[ $i ][ 'function' ] )
				) {
					return $aBt[ $i ][ 'args' ][ 0 ][ 0 ];
				}
				
				if (
					'self' == $aMatches[ 1 ] &&
					( $sClass = $aBt[ $i + 1 ][ 'class' ] )
				) {
					return $sClass;
				}
				
			}
		}
		
	}
	
}



// wrapper for GD's imagecreatefrom* functions that auto-detects image type
function imagecreatefromimgfile( $sImgFile ) {
	
	$sMime = Geko_File_MimeType::get( $sImgFile );
	
	if ( 'image/gif' == $sMime ) {
		$rSource = imagecreatefromgif( $sImgFile );	
	} elseif ( 'image/jpeg' == $sMime ) {
		$rSource = imagecreatefromjpeg( $sImgFile );
	} elseif ( 'image/png' == $sMime ) {
		$rSource = imagecreatefrompng( $sImgFile );	
	} elseif ( 'image/vnd.wap.wbmp' == $sMime ) {
		$rSource = imagecreatefromwbmp( $sImgFile );			
	} else {
		$rSource = NULL;
	}
	
	return $rSource;
	
}



// shorthand for Geko_Html_Element::create()
function _ge( $sElem, $aAtts = array(), $mContent = NULL ) {
	return Geko_Html_Element::create( $sElem, $aAtts, $mContent );
}

// shorthand for Geko_Html_Widget::create()
function _gw( $sWidget, $aAtts, $mValue, $aParams ) {
	return Geko_Html_Widget::create( $sWidget, $aAtts, $mValue, $aParams );
}





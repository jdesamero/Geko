<?php

class Geko_Debug_Backtrace
{

	//
	public static function display() {

		$aDebugFmt = array();
		
		$aDebugs = debug_backtrace();
		
		foreach ( $aDebugs as $aDebug ) {
			$aFmt = array();
			foreach ( $aDebug as $sKey => $mVal ) {
				if ( is_object( $mVal ) ) {
					$aFmt[ $sKey ] = '[' . get_class( $mVal ) . ']';
				} elseif ( 'args' == $sKey ) {
					$aArgs = array();
					foreach ( $mVal as $mArg ) {
						if ( is_object( $mArg ) ) {
							$aArgs[] = '[' . get_class( $mArg ) . ']';
						} elseif ( is_array( $mArg ) && ( $aDebug[ 'function' ] == 'call_user_func_array' ) ) {
							$sCfArg0 = $mArg[ 0 ];
							if ( is_object( $mArg[ 0 ] ) ) $sCfArg0 = '[' . get_class( $mArg[ 0 ] ) . ']';
							$aArgs[] = array( $sCfArg0, $mArg[ 1 ] );
						} else {
							$aArgs[] = $mArg;
						}
					}
					$aFmt[ $sKey ] = $aArgs;
				} else {
					$aFmt[ $sKey ] = $mVal;
				}
			}
			$aDebugFmt[] = $aFmt;
		}
		
		print_r( $aDebugFmt );
		
	}

}

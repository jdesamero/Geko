<?php

// configure error reporting
class Geko_Error
{
	
	//
	public static function start( $iDefaultLevel = NULL, $iScreamLevel = NULL ) {
		
		if ( Geko_Array::getValue( $_REQUEST, '__enable_error_reporting' ) ) {
			
			ini_set( 'display_errors', 1 );
			ini_set( 'scream.enabled', 1 );

			if ( NULL === $iScreamLevel ) {
				$iScreamLevel = E_ALL;
			}
			
			error_reporting( $iScreamLevel );
		
		} else {
			
			ini_set( 'display_errors', 0 );
			
			if ( NULL === $iDefaultLevel ) {
				$iDefaultLevel = E_ALL ^ E_NOTICE;
			}
			
			error_reporting( $iDefaultLevel );
		}
		
	}
	

}



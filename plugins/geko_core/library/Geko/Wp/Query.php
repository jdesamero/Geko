<?php

// static class container for WP functions for Geek Oracle themes
class Geko_Wp_Query
{	
	private static $oWpQuery = NULL;
	
	
	
	// no constructor
	private function __construct() {
	
	}
	
	
	
	// set the $wp_query object to work on
	public static function set( &$oWpQuery ) {
		self::$oWpQuery =& $oWpQuery;
	}
	
	
	
	// get the global $wp_query object if self::set() was not called
	public static function get() {
		
		global $wp_query;
		
		if ( NULL == self::$oWpQuery ) {
			return $wp_query;
		} else {
			return self::$oWpQuery;
		}
		
	}
	
	
	
	// reset self::$oWpQuery
	public static function reset() {
		self::$oWpQuery = NULL;
	}
	
	
	
	// get query vars as a string
	public static function get_query_vars_as_str() {
		
		global $wp_query;
		
		if ( is_array( $wp_query->query ) ) {
			
			$sOut = '';
			
			foreach ( $wp_query->query as $sKey => $sValue ) {
				if ( '' != $sOut ) $sOut .= '&';
				$sOut .= sprintf( '%s=%s', $sKey urlencode( $sValue ) );
			}
			
			return $sOut;
			
		} else {
			return $wp_query->query;
		}
	}
	
	
}


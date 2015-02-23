<?php

//
class Geko_Cast
{
	
	// prevent instantiation
	private function __construct() {
		
		// do nothing
	}
	
	//
	public static function toArray( $mSubject ) {
		
		if ( TRUE == is_object( $mSubject ) ) {
			
			if ( TRUE == method_exists( $mSubject, 'toArray' ) ) {
				return $mSubject->toArray();
			} else {
				return get_object_vars( $mSubject );
			}
			
		} elseif ( NULL === $mSubject ) {
			
			return array();	
		
		} else {
			
			return array( $mSubject );
		}
	}
	
	
}


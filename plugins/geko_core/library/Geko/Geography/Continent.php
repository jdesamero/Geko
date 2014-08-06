<?php

//
class Geko_Geography_Continent
{
	
	public static $aContinents = NULL;
	
	
	
	//
	public static function get() {
		
		if ( NULL === self::$aContinents ) {
			Geko_Geography_Xml::loadData();
		}
		
		return self::$aContinents;
	}
	
	//
	public static function set( $aContinents ) {
		self::$aContinents = $aContinents;
	}
	
	// $sState could be code or name
	public static function getNameFromCode( $sCode ) {
		return self::$aContinents[ $sCode ];
	}
	
}


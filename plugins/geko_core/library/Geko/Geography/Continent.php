<?php

//
class Geko_Geography_Continent
{
	public static $aContinents = array(
		'AF' => 'Africa',
		'AS' => 'Asia',
		'EU' => 'Europe',
		'NA' => 'North America',
		'SA' => 'South America',
		'OC' => 'Oceania',
		'AN' => 'Antarctica'
	);
	
	//
	public static function get() {
		return self::$aContinents;
	}
	
	// $sState could be code or name
	public static function getNameFromCode( $sCode ) {
		return self::$aContinents[ $sCode ];
	}
	
}


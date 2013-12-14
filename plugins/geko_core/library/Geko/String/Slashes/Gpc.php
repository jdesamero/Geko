<?php

class Geko_String_Slashes_Gpc
{
	
	// prevent instantiation
	private function __construct()
	{
		// do nothing
	}
	
	//
	public static function add($sValue)
	{
		return (TRUE == get_magic_quotes_gpc()) ?
			$sValue :										// var has addslashes applied already
			addslashes($sValue)								// magic_quotes_gpc is off
		;
	}
	
	//
	public static function strip($sValue)
	{
		return (TRUE == get_magic_quotes_gpc()) ?
			stripslashes($sValue) :							// magic_quotes_gpc is on
			$sValue											// there are no slashes to strip
		;
	}
	
	//
	public static function addDeep($mValue)
	{
		$mValue = is_array($mValue) ?
			array_map(array('Geko_String_Slashes_Gpc', 'addDeep'), $mValue) :
			self::addSlashes($mValue)
		;
			
		return $mValue;
	}
	
	//
	public static function stripDeep($mValue)
	{
		$mValue = is_array($mValue) ?
			array_map(array('Geko_String_Slashes_Gpc', 'stripDeep'), $mValue) :
			self::stripSlashes($mValue)
		;
			
		return $mValue;
	}
	
}


<?php

//
class Geko_Version
{
	const VERSION = 'TRUNK';
	
	//
	public static function compareVersion($sVersion)
	{
		return (self::VERSION == $sVersion) ? TRUE : FALSE;
	}
	
}


<?php
/*
 * "geko_core/library/Geko/Version.php"
 * https://github.com/jdesamero/Geko
 *
 * Copyright (c) 2013 Joel Desamero.
 * Licensed under the MIT license.
 */

//
class Geko_Version
{
	const VERSION = 'TRUNK';
	
	//
	public static function compareVersion( $sVersion ) {
		return ( self::VERSION == $sVersion ) ? TRUE : FALSE ;
	}
	
}



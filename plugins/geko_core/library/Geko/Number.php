<?php
/*
 * "geko_core/library/Geko/Number.php"
 * https://github.com/jdesamero/Geko
 *
 * Copyright (c) 2013 Joel Desamero.
 * Licensed under the MIT license.
 */

//
class Geko_Number
{
	
	// prevent instantiation
	private function __construct()
	{
		// do nothing
	}
	
	
	// get the ordinal suffix of an integer (st, nd, rd, th)
	public static function ordinalSuffix( $n )
	{
		 $n_last = $n % 100;
		 if (($n_last > 10 && $n_last < 14) || $n == 0){
			  return "{$n}th";
		 }
		 switch(substr($n, -1)) {
			  case '1':    return "{$n}st";
			  case '2':    return "{$n}nd";
			  case '3':    return "{$n}rd";
			  default:     return "{$n}th";
		 }
	}	
	
	
}



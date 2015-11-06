<?php
/*
 * "geko_core/library/Geko/Wp/Util.php"
 * https://github.com/jdesamero/Geko
 *
 * Copyright (c) 2013 Joel Desamero.
 * Licensed under the MIT license.
 *
 * static class container for WP functions for Geek Oracle themes
 */

//
class Geko_Wp_Util
{
	
	//
	public static function in_list( $sNeedle, $mHaystack, $sDelim = ',' ) {
		return Geko_String::inList( $sNeedle, $mHaystack, $sDelim );
	}
	
	
}




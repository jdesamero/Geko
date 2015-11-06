<?php
/*
 * "geko_core/library/Geko/Wp/Date.php"
 * https://github.com/jdesamero/Geko
 *
 * Copyright (c) 2013 Joel Desamero.
 * Licensed under the MIT license.
 */

//
class Geko_Wp_Date
{
	
	// return array values that can be used in a Javascript Date() object
	public function formatDateForRange( $sMysqlDatetime ) {
		
		$aRet = explode( '-', mysql2date( 'Y-n-j', $sMysqlDatetime ) );
		$aRet[ 1 ] = $aRet[ 1 ] - 1;
		
		return $aRet;
	}
	
	
}

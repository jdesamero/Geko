<?php
/*
 * "geko_core/library/Geko/PhpQuery.php"
 * https://github.com/jdesamero/Geko
 *
 * Copyright (c) 2013 Joel Desamero.
 * Licensed under the MIT license.
 *
 * helper static methods for phpQuery
 */

//
class Geko_PhpQuery
{

	public static function last( phpQueryObject $oPq ) {
		
		$oPqLast = NULL;
		
		foreach ( $oPq as $oNode ) {
			$oPqLast = pq( $oNode );
		}
		
		return ( $oPqLast ) ? $oPqLast : $oPq;
	}

}



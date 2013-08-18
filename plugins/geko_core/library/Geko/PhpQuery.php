<?php

// helper static methods for phpQuery
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



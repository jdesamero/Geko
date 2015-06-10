<?php

class Geko_Wp_Date
{
	
	// return array values that can be used in a Javascript Date() object
	public function formatDateForRange( $sMysqlDatetime ) {
		
		$aRet = explode( '-', mysql2date( 'Y-n-j', $sMysqlDatetime ) );
		$aRet[ 1 ] = $aRet[ 1 ] - 1;
		
		return $aRet;
	}
	
	
}

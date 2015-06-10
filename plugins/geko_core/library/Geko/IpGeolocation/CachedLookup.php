<?php

//
class Geko_IpGeolocation_CachedLookup extends Geko_CachedLookup
{
	
	protected $_sDefaultEngineSuffix = 'Db';
	
	
	
	//
	public function getNormalized( $aArgs ) {
		
		$sIpAddress = $aArgs[ 0 ];

		// var_dump( $sIpAddress );
		
		$aIp = explode( '.', $sIpAddress );
		
		array_pop( $aIp );
		
		$sNormIp = sprintf( '%s.0', implode( '.', $aIp ) );
		
		// var_dump( $sNormIp );
		
		$aArgs[ 0 ] = $sNormIp;
		
		return $aArgs;
	}
	
	
	//
	public function getHash( $sNormIp ) {
		
		// var_dump( ip2long( $sNormIp ) );
		
		return ip2long( $sNormIp );
	}
	
	
	
	
}


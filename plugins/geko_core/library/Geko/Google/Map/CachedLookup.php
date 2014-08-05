<?php

//
class Geko_Google_Map_CachedLookup extends Geko_CachedLookup
{
	
	protected $_sDefaultEngineSuffix = 'Db';
	
	
	
	//
	public function getNormalized( $aArgs ) {
		
		$sQuery = $aArgs[ 0 ];
		
		$sNormLoc = str_replace( ';', ',', $sQuery );
		$sNormLoc = trim( $sNormLoc, "-._, \t\n\r\0\x0B" );
		
		$sNormLoc = strtolower( $sNormLoc );
		
		$sNormLoc = preg_replace( '/[\s]+/', ' ', $sNormLoc );
		
		// print_r( $sNormLoc );
		
		$aArgs[ 0 ] = $sNormLoc;
		
		return $aArgs;
	}
	
	
	//
	public function getHash( $sNormLoc ) {
		
		$sHash = md5( $sNormLoc );
		
		// print_r( $sHash );
		
		return $sHash;
	}
	
	
	
	
}


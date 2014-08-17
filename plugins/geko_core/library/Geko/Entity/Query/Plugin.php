<?php

//
class Geko_Entity_Query_Plugin extends Geko_Singleton_Abstract
{
	
	
	//
	public function modifyQuery( $oQuery, $aParams ) {
		
		return $oQuery;
	}
	
	
	//
	public function getSortOrder( $sOrder, $sDefOrder = 'ASC' ) {
		
		$sOrder = strtoupper( trim( $sOrder ) );
		
		if ( in_array( $sOrder, array( 'ASC', 'DESC' ) ) ) {
			return $sOrder;
		}
		
		return $sDefOrder;
	}
	
	
}




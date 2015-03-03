<?php

//
class Geko_Entity_Query_Plugin extends Geko_Singleton_Abstract
{
	
	
	//
	public function modifyQuery( $oQuery, $aParams, $oEntityQuery ) {
		
		return $oQuery;
	}
	
	// called right after getEntities() is called
	public function afterGetEntities( $aEntities, $aParams, $sInvoker, $oEntityQuery ) {
		
		// $sInvoker can either be: Geko_Entity_Query::init, or Geko_Entity_Query::getSingleEntity
		
	}
	
	
	
	//// helpers
	
	//
	public function getImplodedIds( $mIds ) {
		return ( is_array( $mIds ) ) ? implode( ',', $mIds ) : trim( $mIds ) ;
	}
	
	//
	public function getExplodedIds( $mIds ) {
		return ( is_string( $mIds ) ) ? explode( ',', $mIds ) : $mIds ;
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




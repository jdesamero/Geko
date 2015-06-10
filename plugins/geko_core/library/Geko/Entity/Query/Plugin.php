<?php

//
class Geko_Entity_Query_Plugin extends Geko_Singleton_Abstract
{
	
	
	// called right after addPlugin() is invoked on entity query
	public function setupEntityQuery( $oEntityQuery, $aParams ) {
		
	}
	
	
	
	//
	public function modifyQuery( $oQuery, $aParams, $oEntityQuery ) {
		
		return $oQuery;
	}
	
	// called after raw entities and total rows are set
	public function setRawEntities( $aEntities, $aParams, $aData, $oPrimaryTable, $oEntityQuery ) {
		
	}
	
	// called after new entities are added and total rows are recalculated
	public function addRawEntities( $aNewEntities, $aAllEntities, $aParams, $aData, $oPrimaryTable, $oEntityQuery ) {
		
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




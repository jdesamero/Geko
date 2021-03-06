<?php

//
class Geko_Wp_Post_Query_Plugin_Parent extends Geko_Entity_Query_Plugin
{
	
	
	//
	public function modifyQuery( $oQuery, $aParams, $oEntityQuery ) {
		
		// apply super-class manipulations
		$oQuery = parent::modifyQuery( $oQuery, $aParams, $oEntityQuery );
		
		
		if ( $sParentIds = $this->getImplodedIds( $aParams[ 'post_parent__in' ] ) ) {
			$oQuery->where( 'p.post_parent IN (?)', $sParentIds );
		}
		
		
		return $oQuery;
	}
	
	
}




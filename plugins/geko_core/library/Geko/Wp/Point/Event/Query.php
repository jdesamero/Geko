<?php

// listing
class Geko_Wp_Point_Event_Query extends Geko_Wp_Entity_Query
{
	
	protected $_bUseManageQuery = TRUE;
	
	
	
	//
	public function modifyQuery( $oQuery, $aParams ) {
		
		// apply super-class manipulations
		$oQuery = parent::modifyQuery( $oQuery, $aParams );
		
		// point event id
		if ( $aParams[ 'pntevt_id' ] ) {
			$oQuery->where( 'e.pntevt_id = ?', $aParams[ 'pntevt_id' ] );
		}
		
		// point event slug
		if ( $aParams[ 'pntevt_slug' ] ) {
			$oQuery->where( 'e.slug = ?', $aParams[ 'pntevt_slug' ] );
		}
		
		
		return $oQuery;
	}
	
	
}



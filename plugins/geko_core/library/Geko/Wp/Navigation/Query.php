<?php

// listing
class Geko_Wp_Navigation_Query extends Geko_Wp_Entity_Query
{
	
	protected $_bUseManageQuery = TRUE;
	
	
	
	//
	public function getDefaultParams() {
		return $this->setWpQueryVars( 'paged', 'posts_per_page' );
	}
	
	//
	public function modifyQuery( $oQuery, $aParams ) {
		global $wpdb;
		
		// apply super-class manipulations
		$oQuery = parent::modifyQuery( $oQuery, $aParams );
		
		// emsg id
		if ( $aParams[ 'geko_nav_id' ] ) {
			$oQuery->where( 'n.nav_id = ?', $aParams[ 'geko_nav_id' ] );
		}
		
		// emsg slug
		if ( $aParams[ 'geko_nav_slug' ] ) {
			$oQuery->where( 'n.code= ?', $aParams[ 'geko_nav_slug' ] );
		}
		
		return $oQuery;
		
	}
	
	
}


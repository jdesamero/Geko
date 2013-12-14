<?php

// listing
class GekoX_Test_Wp_Full_Query extends Geko_Wp_Entity_Query
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
		if ( $aParams[ 'geko_test_id' ] ) {
			$oQuery->where( 't.test_id = ?', $aParams[ 'geko_test_id' ] );
		}

		// emsg slug
		if ( $aParams[ 'geko_test_slug' ] ) {
			$oQuery->where( 't.slug = ?', $aParams[ 'geko_test_slug' ] );
		}
		
		
		return $oQuery;
	}
	
	
}



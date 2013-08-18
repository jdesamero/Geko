<?php

// listing
class Geko_Wp_EmailMessage_Transport_Query extends Geko_Wp_Entity_Query
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

		// trpt_id
		if ( $aParams[ 'geko_emsg_trpt_id' ] ) {
			$oQuery->where( 't.trpt_id = ?', $aParams[ 'geko_emsg_trpt_id' ] );
		}

		// trpt slug
		if ( $aParams[ 'geko_emsg_trpt_slug' ] ) {
			$oQuery->where( 't.slug = ?', $aParams[ 'geko_emsg_trpt_slug' ] );
		}
		
		return $oQuery;
	}
	
	
}



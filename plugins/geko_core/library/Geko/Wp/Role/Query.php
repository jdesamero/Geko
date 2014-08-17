<?php

// listing
class Geko_Wp_Role_Query extends Geko_Wp_Entity_Query
{
	//// static methods

	//
	public function getDefaultParams() {
		return $this->setWpQueryVars( 'paged', 'posts_per_page' );
	}
	
	//
	public function modifyQuery( $oQuery, $aParams ) {
		
		global $wpdb;
		
		// apply super-class manipulations
		$oQuery = parent::modifyQuery( $oQuery, $aParams );
		
		$oQuery
			->field( 'r.*' )
			->from( $wpdb->geko_roles, 'r' )
		;
		
		// role id
		if ( isset( $aParams['role_id'] ) ) {
			$oQuery->where( 'r.role_id = ?', $aParams['role_id'] );
		}
		
		// role slug
		if ( isset( $aParams['role_slug'] ) ) {
			$oQuery->where( 'r.slug = ?', $aParams['role_slug'] );
		}
		
		// role type
		if ( isset( $aParams['role_type'] ) ) {
			$oQuery->where( 'r.type = ?', $aParams['role_type'] );
		}
		
		// apply default sorting
		if ( !isset( $aParams['orderby'] ) ) {		
			$oQuery
				->order( 'r.type' )
				->order( 'r.title' )
				->order( 'r.slug' )
			;
		}
		
		return $oQuery;
	}

	
}



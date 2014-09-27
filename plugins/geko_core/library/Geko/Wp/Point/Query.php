<?php

// listing
class Geko_Wp_Point_Query extends Geko_Wp_Entity_Query
{
	
	protected $_bUseManageQuery = TRUE;
	
	
	//
	public function modifyParams( $aParams ) {
		
		$aParams = parent::modifyParams( $aParams );
		
		if ( $aParams[ 'kwsearch' ] ) {
			$aParams[ 'kwsearch_fields' ] = array( 'u.user_email', 'ap.title', 'e.name' );
		}
		
		return $aParams;
	}
	
	
	
	//
	public function modifyQuery( $oQuery, $aParams ) {
		
		// apply super-class manipulations
		$oQuery = parent::modifyQuery( $oQuery, $aParams );
		
		$oAppStatQuery = Geko_Wp_Enumeration_Query::getJoinQuery( 'geko-point-status', 'ap' );
		
		$oQuery
			
			->field( 'u.user_email' )
			->joinLeft( '##pfx##users', 'u' )
				->on( 'u.ID = p.user_id' )
			
			->field( 'e.name', 'event' )
			->field( 'e.slug', 'event_slug' )
			->field( 'e.requires_approval' )
			->joinLeft( '##pfx##geko_point_event', 'e' )
				->on( 'e.pntevt_id = p.pntevt_id' )
			
			->field( 'ap.title', 'approve_status' )
			->joinLeft( $oAppStatQuery, 'ap' )
				->on( 'ap.value = p.approve_status_id' )
			
		;
		
		// point id
		if ( $aParams[ 'point_id' ] ) {
			$oQuery->where( 'p.point_id = ?', $aParams[ 'point_id' ] );
		}
		
		// point event id
		if ( $aParams[ 'pntevt_id' ] ) {
			$oQuery->where( 'p.pntevt_id = ?', $aParams[ 'pntevt_id' ] );
		}
		
		// point event slug
		if ( $aParams[ 'pntevt_slug' ] ) {
			$oQuery->where( 'e.slug = ?', $aParams[ 'pntevt_slug' ] );
		}
		
		// user id
		if ( $aParams[ 'user_id' ] ) {
			$oQuery->where( 'p.user_id = ?', $aParams[ 'user_id' ] );
		}
		
		// email
		if ( $aParams[ 'email' ] ) {
			$oQuery->where( 'u.user_email = ?', $aParams[ 'email' ] );		
		}
		
		// requires approval
		if ( $aParams[ 'requires_approval' ] ) {
			$oQuery->where( 'e.requires_approval = ?', $aParams[ 'requires_approval' ] );
		}
		
		if ( isset( $aParams[ 'approve_status_id' ] ) ) {
			$oQuery->where( 'p.approve_status_id = ?', $aParams[ 'approve_status_id' ] );
		}
		
		return $oQuery;
	}
	
	
}
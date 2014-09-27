<?php

// listing
class Geko_Wp_Group_Query extends Geko_Wp_Entity_Query
{
	
	protected $_bUseManageQuery = TRUE;
	protected $_sGroupType;
	
	
	//
	public function modifyParams( $aParams ) {
		
		$aParams = parent::modifyParams( $aParams );
		
		if ( $aParams[ 'kwsearch' ] && !$aParams[ 'kwsearch_override_default_fields' ] ) {
			
			$aParams[ 'kwsearch_fields' ] = array_diff( Geko_Array::merge(
				$aParams[ 'kwsearch_fields' ],
				array( 'g.title', 'g.description' )
			), array( '' ) );
		}
		
		return $aParams;
	}
	
	
	//
	public function getDefaultParams() {
		
		$aRet = $this->setWpQueryVars( 'paged', 'posts_per_page', 'geko_role_slug' );
		
		if ( !$aRet[ 'geko_group_type' ] && $this->_sGroupType ) {
			$aRet[ 'geko_group_type' ] = $this->_sGroupType;
		}
		
		return $aRet;
	}
	
	
	
	//
	public function modifyQuery( $oQuery, $aParams ) {
		
		// apply super-class manipulations
		$oQuery = parent::modifyQuery( $oQuery, $aParams );
		
		
		$oQuery
			->field( 'r.role_id' )
			->field( 'r.title', 'role_title' )
			->joinLeft( '##pfx##geko_roles', 'r' )
				->on( 'r.role_id = g.role_id' )
		;
		
		//// 
		
		// group id
		if ( $aParams[ 'geko_group_id' ] ) {
			$oQuery->where( 'g.group_id * ($)', $aParams[ 'geko_group_id' ] );
		}

		// user slug
		if ( $aParams[ 'geko_group_slug' ] ) {
			$oQuery->where( 'g.slug = ?', $aParams[ 'geko_group_slug' ] );
		}
		
		// group_type, grptype_id
		if ( $aParams[ 'geko_group_type' ] ) {
			$aParams[ 'geko_group_type_id' ] = Geko_Wp_Options_MetaKey::getId( $aParams[ 'geko_group_type' ] );
		}
		
		if ( $aParams[ 'geko_group_type_id' ] ) {
			$oQuery->where( 'g.grptype_id = ?', $aParams[ 'geko_group_type_id' ] );
		}

		// parent id
		if ( $aParams[ 'parent_id' ] ) {
			$oQuery->where( 'g.parent_id = ?', $aParams[ 'parent_id' ] );
		}
		
		
		//// role filters
		
		// role filter
		if ( $aParams[ 'geko_role_id' ] ) {
			$oQuery->where( 'g.role_id = ?', $aParams[ 'geko_role_id' ] );
		}
		
		// role filter
		if ( $aParams[ 'geko_role_slug' ] && ( 'all' != $aParams[ 'geko_role_slug' ] ) ) {
			$oQuery->where( 'r.slug = ?', $aParams[ 'geko_role_slug' ] );
		}
		
		
		//// advanced search filters
		
		//
		if ( $aParams[ 'title' ] ) {
			$oQuery->where( 'g.title LIKE ?', '%' . $aParams[ 'title' ] . '%' );
		}
		
		
		
		// apply default sorting
		if ( !isset( $aParams[ 'orderby' ] ) ) {		
			$oQuery
				->order( 'g.grptype_id' )
				->order( 'g.title' )
			;
		}
		
		
		return $oQuery;
	}
	
	
	
}



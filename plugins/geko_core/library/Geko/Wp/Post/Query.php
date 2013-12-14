<?php

class Geko_Wp_Post_Query extends Geko_Wp_Entity_Query
{
	protected $oWpQuery;
	
	//
	public function getDefaultParams() {
		
		global $wp_query;
		
		$aDefaultParams = parent::getDefaultParams();
		
		// HACK!!! Hackity hack hack...
		if (
			( $aDefaultParams[ 'category_name' ] ) && 
			( $sCatName = $wp_query->query_vars[ 'category_name' ] )
		) {
			$aDefaultParams[ 'category_name' ] = $sCatName;
		}
		
		return array_merge(
			$aDefaultParams,
			$this->setWpQueryVars( 'paged', 'posts_per_page' )
		);
		
	}
	
	// implement by sub-class to populate entities/total rows
	public function init() {
		
		// if using non-standard parameters, use our own query
		if ( $this->_aParams[ 'use_non_native_query' ] ) {
			return parent::init();
		}
		
		global $wp_query;
		
		if ( $this->_bIsDefaultQuery ) {
			$this->oWpQuery = $wp_query;
		} else {
			$aParams = $this->_aParams;
			if ( $this->_bAddToDefaultParams ) {
				$aParams = array_merge( $this->getDefaultParams(), $aParams );
			}
			$this->oWpQuery = new WP_Query( $aParams );		
		}
		
		$this->_aEntities = $this->oWpQuery->posts;
		$this->_iTotalRows = $this->oWpQuery->found_posts;
		
		// HACK!!! Hackity hack hack...
		$iItemsPerPage = intval( $this->_aParams[ 'posts_per_page' ] );
		if ( $iItemsPerPage > 0 ) {
			$wp_query->found_posts = $this->_iTotalRows;
			$wp_query->max_num_pages = ceil( $this->_iTotalRows / $iItemsPerPage );
		}
		
		return $this;
	}
	
	
	//
	public function getSqlQuery() {
		return ( $this->oWpQuery ) ? $this->oWpQuery->request : '';
	}
	
	
	//
	public function getSingleEntity( $mParam ) {
		
		$aRes = query_posts( $mParam );
		
		if ( count( $aRes ) > 0 ) {
			wp_reset_query();
			return $aRes[ 0 ];
		}
		
		return NULL;
	}
	
	// only kicks in when "use_non_native_query" is set to TRUE
	public function modifyQuery( $oQuery, $aParams ) {
		
		global $wpdb;
		
		// apply super-class manipulations
		$oQuery = parent::modifyQuery( $oQuery, $aParams );
		
		$oQuery
			
			->field( 'p.ID', 'id' )
			->field( 'p.post_author' )
			->field( 'p.post_date' )
			->field( 'p.post_date_gmt' )
			->field( 'p.post_content' )
			->field( 'p.post_title' )
			->field( 'p.post_excerpt' )
			->field( 'p.post_status' )
			->field( 'p.comment_status' )
			->field( 'p.ping_status' )
			->field( 'p.post_password' )
			->field( 'p.post_name' )
			->field( 'p.to_ping' )
			->field( 'p.pinged' )
			->field( 'p.post_modified' )
			->field( 'p.post_modified_gmt' )
			->field( 'p.post_content_filtered' )
			->field( 'p.post_parent' )
			->field( 'p.guid' )
			->field( 'p.menu_order' )
			->field( 'p.post_type' )
			->field( 'p.post_mime_type' )
			->field( 'p.comment_count' )
			
			->from( $wpdb->posts, 'p' )
		;
		
		if ( isset( $aParams[ 'id' ] ) ) {
			$oQuery->where( 'p.ID * ($)', $aParams[ 'id' ] );
		}
		
		if ( isset( $aParams[ 'post_type' ] ) ) {
			$oQuery->where( 'p.post_type * ($)', $aParams[ 'post_type' ] );
		}
		
		return $oQuery;
		
	}
	
}





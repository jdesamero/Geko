<?php

abstract class Geko_Wp_Entity_Query extends Geko_Entity_Query
{
	
	protected $_bUseManageQuery = FALSE;
	
	
	
	// implement by sub-class to populate entities/total rows
	public function init() {
		
		global $wp_query;
		
		$this->_sSqlQuery = $this->constructQuery( $this->_aParams );
		$this->_aEntities = $this->getEntities( $this->_sSqlQuery );	
		$this->_iTotalRows = $this->getFoundRows();
		
		// HACK!!! Hackity hack hack...
		$iItemsPerPage = intval( $this->_aParams[ 'posts_per_page' ] );
		if ( $iItemsPerPage > 0 ) {
			$wp_query->found_posts = $this->_iTotalRows;
			$wp_query->max_num_pages = ceil( $this->_iTotalRows / $iItemsPerPage );
		}
		
		return $this;
	}
	
	//
	public function modifyParams( $aParams ) {
		return apply_filters( get_class( $this ) . '::__construct::aParams', $aParams, $this );	
	}
	
	
	
	//// initial helpers
	
	//
	public function getDefaultParams() {
		
		global $query_string;
		
		$aDefaultParams = array();
		parse_str( $query_string, $aDefaultParams );
		
		return $aDefaultParams;
	}
	
	
	
	//// helper methods
	
	//
	protected function setWpQueryVars() {
		
		global $wp_query;
		
		$aRet = array();
		$aQueryVars = func_get_args();
		
		foreach ( $aQueryVars as $sVar ) {
			if ( isset( $wp_query->query_vars[ $sVar ] ) ) {
				$aRet[ $sVar ] = $wp_query->query_vars[ $sVar ];
			}		
		}
		
		return $aRet;
	}
	
	
	
	//// working with sets
	
	
	//// query methods
	
	//
	public function modifyQuery( $oQuery, $aParams ) {
		
		// apply super-class manipulations
		$oQuery = parent::modifyQuery( $oQuery, $aParams );
		
		// wordpress paging
		$iItemsPerPage = intval( $aParams[ 'posts_per_page' ] );
		if (
			( isset( $aParams[ 'paged' ] ) ) && 
			( $iItemsPerPage > 0 )
		) {
			$iItemsPerPage = intval( $aParams[ 'posts_per_page' ] );
			$iOffset = (
				( ( $aParams[ 'paged' ] ) ? ( intval( $aParams[ 'paged' ] ) - 1 ) : 0 ) *
				$iItemsPerPage
			);
			$oQuery->limitOffset( $iItemsPerPage, $iOffset );
		}
		
		
		//// keyword search
		if ( $aParams[ 'kwsearch' ] ) {
			$oQuery->where( Geko_Wp_Db::keywordSearch(
				$aParams[ 'kwsearch' ], $aParams[ 'kwsearch_fields' ]
			) );
		}
				
		return $oQuery;
	}
	
	
	//
	public function getFoundRows() {
		global $wpdb;
		return $wpdb->get_var( 'SELECT FOUND_ROWS()' );
	}
	
	//
	public function getEntities( $mParam ) {
		
		global $wpdb;
		
		if ( $this->_bProfileQuery ) {
			echo $this->getEntityQuery( $mParam );
			return array();
		} else {
			return $wpdb->get_results(
				$this->getEntityQuery( $mParam )
			);
		}
	}
	
	//
	public function getSingleEntity( $mParam ) {
		global $wpdb;
		return $wpdb->get_row(
			$this->getEntityQuery( $mParam )
		);
	}
	
	
	
	
	
	//// rail functionality
	
	//
	public function renderListing() {
		if ( $this->_sManageClass ) {
			$oMng = Geko_Singleton_Abstract::getInstance( $this->_sManageClass );
			$oMng->renderListing( $this );
		}
	}
	
	
	
}


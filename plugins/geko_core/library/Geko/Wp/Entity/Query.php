<?php

abstract class Geko_Wp_Entity_Query extends Geko_Entity_Query
{
	
	protected $_bUseManageQuery = FALSE;
	
	
	
	// implement by sub-class to populate entities/total rows
	public function init() {
		
		$this->initHooks();
		
		parent::init();
		
		global $wp_query;
		
		// HACK!!! Hackity hack hack...
		$iItemsPerPage = intval( $this->_aParams[ 'posts_per_page' ] );
		if ( $iItemsPerPage > 0 ) {
			$wp_query->found_posts = $this->_iTotalRows;
			$wp_query->max_num_pages = ceil( $this->_iTotalRows / $iItemsPerPage );
		}
		
		return $this;
	}
	
	//
	public function initHooks() {
		
		do_action( sprintf( '%s::init', $this->_sQueryClass ), $this );
		
		return $this;
	}
	
	//
	public function modifyParams( $aParams ) {
		return apply_filters( sprintf( '%s::__construct::aParams', get_class( $this ) ), $aParams, $this );	
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
		
		
		// to use with FOUND_ROWS()
		$oQuery->option( 'SQL_CALC_FOUND_ROWS' );
		
		
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
			
			if ( is_array( $aParams[ 'kwsearch_override_default_fields' ] ) ) {
				// override completely
				$aParams[ 'kwsearch_fields' ] = $aParams[ 'kwsearch_override_default_fields' ];
				unset( $aParams[ 'kwsearch_override_default_fields' ] );
			}
			
			$oQuery->where( Geko_Wp_Db::keywordSearch(
				$aParams[ 'kwsearch' ], $aParams[ 'kwsearch_fields' ]
			) );
		}
		
		
		//// force empty result set
		if ( $aParams[ 'force_empty' ] ) {
			
			$oQuery
				->unsetClause()
				->field( 'NULL' )
				->limit( 0 )
			;
		}
		
		
		return $oQuery;
	}
	
	
	//
	public function getFoundRows() {
		
		$oDb = Geko_Wp::get( 'db' );
		
		return $oDb->fetchOne( 'SELECT FOUND_ROWS()' );
	}
	
	//
	public function getEntities( $mParam ) {
		
		$oDb = Geko_Wp::get( 'db' );
		
		if ( $this->_bProfileQuery ) {
			
			echo $this->getEntityQuery( $mParam );
			return array();
			
		} else {
			
			return $oDb->fetchAllObj(
				$this->getEntityQuery( $mParam )
			);
		}
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


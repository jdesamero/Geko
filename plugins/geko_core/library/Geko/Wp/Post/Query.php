<?php

class Geko_Wp_Post_Query extends Geko_Wp_Entity_Query
{
	
	protected $oWpQuery;
	
	protected static $sQhVar = 'geko_post_query_hooks';
	protected static $bInitQueryHooks = FALSE;
	
	
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
		
		$this->initHooks();
		
		global $wp_query;
		
		if ( $this->_bIsDefaultQuery ) {
			
			$this->oWpQuery = $wp_query;
		
		} else {
			
			$aParams = $this->_aParams;
			
			if ( $this->_bAddToDefaultParams ) {
				$aParams = array_merge( $this->getDefaultParams(), $aParams );
			}
			
			$aParams = $this->setUpWpQueryFilters( $aParams );
			
			$this->oWpQuery = new WP_Query( $aParams );
		}
		
		$this->setRawEntities( $this->oWpQuery->posts );
		
		
		// HACK!!! Hackity hack hack...
		$iItemsPerPage = intval( $this->_aParams[ 'posts_per_page' ] );
		
		if ( $iItemsPerPage > 0 ) {
			
			$wp_query->found_posts = $this->_iTotalRows;
			$wp_query->max_num_pages = ceil( $this->_iTotalRows / $iItemsPerPage );
		}
		
		return $this;
	}
	
	//
	public function setUpWpQueryFilters( $aParams ) {

		$oQuery = $this->constructQuery( $aParams, TRUE );
		
		if ( $oQuery->isMutated() ) {
			self::initQueryHooks();
			$aParams[ self::$sQhVar ] = $oQuery;
		}
		
		return $aParams;
	}
	
	//
	public function getSqlQuery() {
		return ( $this->oWpQuery ) ? $this->oWpQuery->request : '' ;
	}
	
	//
	public function getWpQuery() {
		return $this->oWpQuery;
	}
	
	//
	public function getFoundRows() {
		return $this->oWpQuery->found_posts;
	}
	
	
	
	// only kicks in when "use_non_native_query" is set to TRUE
	public function modifyQuery( $oQuery, $aParams ) {
		
		// short circuit this
		if ( !$aParams[ 'use_non_native_query' ] ) {
			return $oQuery;
		}
		
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
			
			->from( '##pfx##posts', 'p' )
		;
		
		if ( isset( $aParams[ 'id' ] ) ) {
			$oQuery->where( 'p.ID * ($)', $aParams[ 'id' ] );
		}
		
		if ( isset( $aParams[ 'post_type' ] ) ) {
			$oQuery->where( 'p.post_type * ($)', $aParams[ 'post_type' ] );
		}
		
		return $oQuery;
		
	}
	
	
	
	//// static query hook methods
	
	
	// wp-includes/query.php
	
	// do_action_ref_array('pre_get_posts', array(&$this));
	// $search = apply_filters_ref_array( 'posts_search', array( $search, &$this ) );
	// $where = apply_filters_ref_array('posts_where', array( $where, &$this ) );
	// $join = apply_filters_ref_array('posts_join', array( $join, &$this ) );
	
	//// first
	
	// $where		= apply_filters_ref_array( 'posts_where_paged',	array( $where, &$this ) );
	// $groupby	= apply_filters_ref_array( 'posts_groupby',		array( $groupby, &$this ) );
	// $join		= apply_filters_ref_array( 'posts_join_paged',	array( $join, &$this ) );
	// $orderby	= apply_filters_ref_array( 'posts_orderby',		array( $orderby, &$this ) );
	// $distinct	= apply_filters_ref_array( 'posts_distinct',	array( $distinct, &$this ) );
	// $limits		= apply_filters_ref_array( 'post_limits',		array( $limits, &$this ) );
	// $fields		= apply_filters_ref_array( 'posts_fields',		array( $fields, &$this ) );
	
	// do_action( 'posts_selection', $where . $groupby . $orderby . $limits . $join );

	//// second, caching plugins
	
	// $where		= apply_filters_ref_array( 'posts_where_request',		array( $where, &$this ) );
	// $groupby	= apply_filters_ref_array( 'posts_groupby_request',		array( $groupby, &$this ) );
	// $join		= apply_filters_ref_array( 'posts_join_request',		array( $join, &$this ) );
	// $orderby	= apply_filters_ref_array( 'posts_orderby_request',		array( $orderby, &$this ) );
	// $distinct	= apply_filters_ref_array( 'posts_distinct_request',	array( $distinct, &$this ) );
	// $fields		= apply_filters_ref_array( 'posts_fields_request',		array( $fields, &$this ) );
	// $limits		= apply_filters_ref_array( 'post_limits_request',		array( $limits, &$this ) );

	// Filter all clauses at once, for convenience
	// $clauses = (array) apply_filters_ref_array( 'posts_clauses_request', array( compact( $pieces ), &$this ) );

	
	//
	public static function initQueryHooks() {
		
		if ( !self::$bInitQueryHooks ) {
			
			add_action( 'pre_get_posts', array( __CLASS__, 'qhPreGetPosts' ) );
			
			add_filter( 'posts_search', array( __CLASS__, 'qhPostsSearch' ), 10, 2 );
			add_filter( 'posts_where', array( __CLASS__, 'qhPostsWhere' ), 10, 2 );
			add_filter( 'posts_join', array( __CLASS__, 'qhPostsJoin' ), 10, 2 );
			add_filter( 'posts_where_paged', array( __CLASS__, 'qhPostsWherePaged' ), 10, 2 );
			add_filter( 'posts_groupby', array( __CLASS__, 'qhPostsGroupBy' ), 10, 2 );
			add_filter( 'posts_join_paged', array( __CLASS__, 'qhPostsJoinPaged' ), 10, 2 );
			add_filter( 'posts_orderby', array( __CLASS__, 'qhPostsOrderBy' ), 10, 2 );
			add_filter( 'posts_distinct', array( __CLASS__, 'qhPostsDistinct' ), 10, 2 );
			add_filter( 'posts_fields', array( __CLASS__, 'qhPostsFields' ), 10, 2 );
			add_filter( 'post_limits', array( __CLASS__, 'qhPostLimits' ), 10, 2 );
			add_filter( 'posts_request', array( __CLASS__, 'qhPostsRequest' ), 10, 2 );
			
			self::$bInitQueryHooks = TRUE;
		}
		
	}	
	
	
	//
	public static function qhPreGetPosts( $oWpQuery ) {
		
	}
	
	//
	public static function qhPostsSearch( $sSearch, $oWpQuery ) {
		
		if ( $oQuery = $oWpQuery->get( self::$sQhVar ) ) {
			
		}
		
		return $sSearch;
	}
	
	//
	public static function qhPostsWhere( $sWhere, $oWpQuery ) {
		
		if (
			( $oQuery = $oWpQuery->get( self::$sQhVar ) ) &&
			( $sQhWhere = $oQuery->getWhere() )
		) {
			$sWhere .= sprintf( ' AND %s ', self::replaceReferences( $sQhWhere ) );
		}
		
		return $sWhere;
	}
	
	//
	public static function qhPostsJoin( $sJoin, $oWpQuery ) {
		
		if (
			( $oQuery = $oWpQuery->get( self::$sQhVar ) ) &&
			( $sQhJoin = $oQuery->getJoins() )
		) {			
			$sJoin .= sprintf( ' %s', self::replaceReferences( $sQhJoin ) );
		}
		
		return $sJoin;
	}
	
	//
	public static function qhPostsWherePaged( $sWherePaged, $oWpQuery ) {
		
		if ( $oQuery = $oWpQuery->get( self::$sQhVar ) ) {
			
		}
		
		return $sWherePaged;
	}
	
	//
	public static function qhPostsGroupBy( $sGroupBy, $oWpQuery ) {
		
		if ( $oQuery = $oWpQuery->get( self::$sQhVar ) ) {

			$sQhGroup = self::replaceReferences( $oQuery->getGroup() );
			$sQhHaving = self::replaceReferences( $oQuery->getHaving() );
			
			if ( $sQhGroup || $sQhHaving ) {
				
				$aRegs = array();
				
				$sHaving = '';
				
				if ( preg_match( '/(.*?)(having)(.+)/msi', $sGroupBy, $aRegs ) ) {
					
					// has "HAVING" clause
					$sGroupBy = trim( $aRegs[ 1 ] );
					$sHaving = trim( sprintf( '%s%s', $aRegs[ 2 ], $aRegs[ 3 ] ) );
					
				}
				
				//// TO DO: allow overrides???
				
				// additional group clause
				if ( $sQhGroup ) {
					if ( $sGroupBy ) {
						$sGroupBy = sprintf( '%s, %s', $sGroupBy, $sQhGroup );
					} else {
						$sGroupBy = $sQhGroup;
					}
				}
				
				// additional having clause
				if ( $sQhHaving ) {
					if ( $sHaving ) {
						$sHaving = sprintf( '%s AND %s', $sHaving, $sQhHaving );
					} else {
						$sHaving = sprintf( 'HAVING %s', $sQhHaving );
					}
				}
				
				$sClause = trim( sprintf( '%s %s', $sGroupBy, $sHaving ) );
				
				$sGroupBy = sprintf( ' %s', $sClause );
			}
			
		}
		
		
		return $sGroupBy;
	}
	
	//
	public static function qhPostsJoinPaged( $sJoinPaged, $oWpQuery ) {
		
		if ( $oQuery = $oWpQuery->get( self::$sQhVar ) ) {
			
		}
		
		return $sJoinPaged;
	}
	
	//
	public static function qhPostsOrderBy( $sOrderBy, $oWpQuery ) {
		
		if (
			( $oQuery = $oWpQuery->get( self::$sQhVar ) ) && 
			( $sQhOrder = $oQuery->getOrder() )
		) {
			
			// TO DO: handle overrides/additions?
			
			$sOrderBy = self::replaceReferences( $sQhOrder );
		}
		
		return $sOrderBy;
	}
	
	//
	public static function qhPostsDistinct( $sDistinct, $oWpQuery ) {
		
		if ( $oQuery = $oWpQuery->get( self::$sQhVar ) ) {
			
		}
		
		return $sDistinct;
	}
	
	//
	public static function qhPostsFields( $sFields, $oWpQuery ) {

		if (
			( $oQuery = $oWpQuery->get( self::$sQhVar ) ) && 
			( $sQhFields = $oQuery->getFields() )
		) {
			$sFields .= sprintf( ', %s', self::replaceReferences( $sQhFields ) );
		}
		
		return $sFields;
	}
	
	//
	public static function qhPostLimits( $sLimits, $oWpQuery ) {

		if ( $oQuery = $oWpQuery->get( self::$sQhVar ) ) {
			
		}
		
		return $sLimits;
	}
	
	//
	public static function qhPostsRequest( $sRequest, $oWpQuery ) {

		if ( $oQuery = $oWpQuery->get( self::$sQhVar ) ) {
			
		}
		
		return $sRequest;
	}
	
	
	//// helpers
	
	//
	public static function replaceReferences( $sValue ) {
		
		$sValue = sprintf( ' %s ', $sValue );
		
		$aRegs = array();
		
		if ( preg_match_all( '/([^a-zA-Z_])p\.([a-zA-Z_]+[^a-zA-Z_])/ms', $sValue, $aRegs ) ) {
			
			$aRegsFmt = Geko_Array::formatPregMatchAll( $aRegs, array( 'full', 'pre', 'post' ) );
			
			foreach ( $aRegsFmt as $aRow ) {
				$sValue = str_replace( $aRow[ 'full' ], sprintf( '%s%s.%s', $aRow[ 'pre' ], '##pfx##posts', $aRow[ 'post' ] ), $sValue );
			}
		}
		
		if ( $oDb = Geko_Wp::get( 'db' ) ) {
			$sValue = $oDb->replacePrefixPlaceholder( $sValue );
		}
		
		return trim( $sValue );
	}
	
	
}





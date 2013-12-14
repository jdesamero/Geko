<?php

// static class container for $wp_query hooks
abstract class Geko_Wp_Query_Hooks_Abstract
{
	protected static $oWpQuery;
	protected static $aRegd = array();
	protected static $sJoinKeyPrefix = '_j';
	
	//
	public static function setJoinKeyPrefix( $sJoinKeyPrefix ) {
		self::$sJoinKeyPrefix = $sJoinKeyPrefix;
	}

	//
	public static function getJoinKeyPrefix() {
		return self::$sJoinKeyPrefix;
	}
	
	
	
	//
	public static function register( $sClassName, $aParams = array() ) {
		
		static $bCalled = FALSE;
		
		if ( !$bCalled ) {
			Geko_Wp_Hooks::attachGekoHookFilters(
				'posts_search',
				'posts_where',
				'posts_join',
				'posts_where_paged',
				'posts_groupby',
				'posts_join_paged',
				'posts_orderby',
				'posts_distinct',
				'posts_fields',
				'post_limits',
				'posts_request'
			);
			$bCalled = TRUE;
		}
		
		// create a unique id for a particular hook
		$sRegHash = self::getRegHash( $sClassName, $aParams );
		
		if ( !isset( self::$aRegd[ $sRegHash ] ) ) {
			
			// first arg is the subject
			$aPassed = array( 0, $aParams );
			
			// hooks are called in this weird order
			add_action( 'pre_get_posts', array( $sClassName, 'pre_get_posts' ) );
			
			Geko_Hooks::addFilter( 'posts_search', array( $sClassName, 'where_search' ), $aPassed );				
			Geko_Hooks::addFilter( 'posts_where', array( $sClassName, 'where' ), $aPassed );				
			Geko_Hooks::addFilter( 'posts_join', array( $sClassName, 'join' ), $aPassed );
			Geko_Hooks::addFilter( 'posts_where_paged', array( $sClassName, 'where_paged' ), $aPassed );				
			Geko_Hooks::addFilter( 'posts_groupby', array( $sClassName, 'groupby' ), $aPassed );
			Geko_Hooks::addFilter( 'posts_join_paged', array( $sClassName, 'join_paged' ), $aPassed );			
			Geko_Hooks::addFilter( 'posts_orderby', array( $sClassName, 'orderby' ), $aPassed );
			Geko_Hooks::addFilter( 'posts_distinct', array( $sClassName, 'distinct' ), $aPassed );		
			Geko_Hooks::addFilter( 'posts_fields', array( $sClassName, 'fields' ), $aPassed );
			Geko_Hooks::addFilter( 'post_limits', array( $sClassName, 'limits' ), $aPassed );	
			Geko_Hooks::addFilter( 'posts_request', array( $sClassName, 'request' ), $aPassed );		
			
			self::$aRegd[ $sRegHash ] = count( self::$aRegd );
		}
	}
	
	
	//
	public static function getJoinKey( $sClassName, $aParams = array() ) {
		$sRegHash = self::getRegHash( $sClassName, $aParams );
		return self::$sJoinKeyPrefix . self::$aRegd[ $sRegHash ];
	}
	
	//
	protected static function getRegHash( $sClassName, $aParams = array() ) {
		return $sClassName . serialize( $aParams );
	}
	
	
	// empty hook methods
	
	//
	public static function pre_get_posts( $oWpQuery ) {
		self::$oWpQuery = $oWpQuery;
	}

	//
	public static function where_search( $sValue, $aParams = array() ) {
		return $sValue;
	}
	
	//
	public static function where( $sValue, $aParams = array() ) {
		return $sValue;
	}
	
	//
	public static function join( $sValue, $aParams = array() ) {
		return $sValue;
	}

	//
	public static function where_paged( $sValue, $aParams = array() ) {
		return $sValue;
	}

	//
	public static function groupby( $sValue, $aParams = array() ) {
		return $sValue;
	}

	//
	public static function join_paged( $sValue, $aParams = array() ) {
		return $sValue;
	}

	//
	public static function orderby( $sValue, $aParams = array() ) {
		
		if ( $sOrderClause = self::$oWpQuery->query_vars[ 'order_clause' ] ) {
			return $sOrderClause;
		}
		
		return $sValue;
	}
	
	//
	public static function distinct( $sValue, $aParams = array() ) {
		return $sValue;
	}

	//
	public static function fields( $sValue, $aParams = array() ) {
		return $sValue;
	}
	
	//
	public static function limits( $sValue, $aParams = array() ) {
		return $sValue;
	}
	
	//
	public static function request( $sValue, $aParams = array() ) {
		return $sValue;
	}
	
		
	
	//
	public static function set_geko_raw_query_vars() {
		
		if ( FALSE == is_array( self::$oWpQuery->geko_raw_query_vars ) ) {
			if ( '' == self::$oWpQuery->query ) {
				self::$oWpQuery->geko_raw_query_vars = array();
			} else {
				if ( TRUE == is_array( self::$oWpQuery->query ) ) {
					self::$oWpQuery->geko_raw_query_vars = self::$oWpQuery->query;
				} else {
					parse_str( self::$oWpQuery->query, self::$oWpQuery->geko_raw_query_vars );
				}
			}
		}
	}
	
	
	//// helpers
	
	// there's no hook for a having clause, so we'll have to attach to a GROUP BY clause
	// this will test is there is a HAVING clause already set
	public static function hasHaving( $sGroupBy ) {
		return ( FALSE !== strpos( strtolower( $sGroupBy ), 'having' ) );
	}
	
	
}



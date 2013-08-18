<?php

// adds "include_ids" and "exclude_ids" functionality with the query_posts() function
class Geko_Wp_Query_Hooks_IncludeExcludePosts extends Geko_Wp_Query_Hooks_Abstract
{
	//
	public static function where( $sWhere ) {
		
		global $wpdb;
		
		if ( $mIncludeIds = self::$oWpQuery->query_vars[ 'include_ids' ] ) {
			$sIncludeIds = ( is_array( $mIncludeIds ) ) ?
				implode( ',', $mIncludeIds ) : 
				$mIncludeIds
			;
		}
		
		if ( '' != $sIncludeIds ) {
			$sWhere .= " AND {$wpdb->posts}.ID IN ($sIncludeIds) ";
		}
		
		if ( $mExcludeIds = self::$oWpQuery->query_vars[ 'exclude_ids' ] ) {
			$sExcludeIds = ( is_array( $mExcludeIds ) ) ?
				implode( ',', $mExcludeIds ) : 
				$mExcludeIds
			;
		}
		
		if ( '' != $sExcludeIds ) {
			$sWhere .= " AND {$wpdb->posts}.ID NOT IN ($sExcludeIds) ";
		}
		
		return $sWhere;
	}
	
	//
	public static function orderby( $sOrderBy ) {
		
		global $wpdb;
		
		self::set_geko_raw_query_vars();
		
		if (
			( '' != self::$oWpQuery->query_vars[ 'include_ids' ] ) &&
			( 'include_ids' == self::$oWpQuery->geko_raw_query_vars[ 'orderby' ] )
		) {
			$sOrder = self::$oWpQuery->geko_raw_query_vars[ 'order' ];
			if ( '' == $sOrder ) $sOrder = 'ASC';
			
			$sOrderBy = " include_ids_order $sOrder ";
		}
		
		return $sOrderBy;
	}
	
	//
	public static function fields( $sFields ) {
		
		global $wpdb;
		
		$mIncludeIds = self::$oWpQuery->query_vars[ 'include_ids' ];
		
		if ( '' != $mIncludeIds ) {
			
			$sFields .= ", CASE {$wpdb->posts}.ID ";
			
			$aIncludeIds = ( is_string( $mIncludeIds ) ) ?
				explode( ',', $mIncludeIds ) : 
				$mIncludeIds
			;
			
			foreach ( $aIncludeIds as $i => $iID ) {
				$sFields .= " WHEN $iID THEN $i ";
			}
			
			$sFields .= " ELSE NULL END AS include_ids_order";		
		}
		
		return $sFields;
	}
	
	
	
	//
	public static function register() {
		parent::register( __CLASS__ );
	}
	
	
}


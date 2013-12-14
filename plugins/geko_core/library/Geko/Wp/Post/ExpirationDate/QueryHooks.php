<?php

// adds "start_date" and "expiry_date" fields to the query_posts()
class Geko_Wp_Post_ExpirationDate_QueryHooks extends Geko_Wp_Query_Hooks_Abstract
{
	protected static $bJoin = FALSE;
	
	
	
	//
	public static function where($where)
	{
		global $wpdb;
		$sJoinKey = self::getJoinKey();
		
		// determine whether the join needs to be made
		self::set_geko_raw_query_vars();
		
		$aQueryVars = self::$oWpQuery->query_vars;
		$aGekoRawQueryVars = self::$oWpQuery->geko_raw_query_vars;
		
		if (
			$aQueryVars['hide_expired'] ||
			$aQueryVars['hide_unexpired'] ||
			
			$aQueryVars['add_start_date_field'] || 
			$aQueryVars['add_expiry_date_field'] || 
			$aQueryVars['add_is_expired_field'] || 

			$aQueryVars['where_start_day'] || 
			$aQueryVars['where_start_monthnum'] || 
			$aQueryVars['where_start_year'] || 

			$aQueryVars['where_expiry_day'] || 
			$aQueryVars['where_expiry_monthnum'] || 
			$aQueryVars['where_expiry_year'] || 
			
			$aQueryVars['where_min_date'] || 
			$aQueryVars['where_max_date'] || 
			$aQueryVars['where_between_date'] ||
			
			'start_date' == $aGekoRawQueryVars['orderby'] || 
			'expiry_date' == $aGekoRawQueryVars['orderby']
		) {
			self::$bJoin = TRUE;
		} else {
			self::$bJoin = FALSE;
		}
		
		// - - - - -
		
		if ( self::$bJoin ) {
		
			if ( $aQueryVars['hide_expired'] ) {
				$where .= " AND ( COALESCE($sJoinKey.expiry_date, {$wpdb->posts}.post_date) > NOW() ) ";		
			}
	
			if ( $aQueryVars['hide_unexpired'] ) {
				$where .= " AND ( COALESCE($sJoinKey.expiry_date, {$wpdb->posts}.post_date) <= NOW() ) ";		
			}
			
			
			
			if ( $aQueryVars['where_start_day'] ) {
				$where .= $wpdb->prepare(
					" AND ( %d = COALESCE( DAY( $sJoinKey.start_date ), DAY( {$wpdb->posts}.post_date ) ) ) ",
					$aQueryVars['where_start_day']
				);
			}
			
			if ( $aQueryVars['where_start_monthnum'] ) {
				$where .= $wpdb->prepare(
					" AND ( %d = COALESCE( MONTH( $sJoinKey.start_date ), MONTH( {$wpdb->posts}.post_date ) ) ) ",
					$aQueryVars['where_start_monthnum']
				);		
			}

			if ( $aQueryVars['where_start_year'] ) {
				$where .= $wpdb->prepare(
					" AND ( %d = COALESCE( YEAR( $sJoinKey.start_date ), YEAR( {$wpdb->posts}.post_date ) ) ) ",
					$aQueryVars['where_start_year']
				);		
			}
			
			
			
			if ( $aQueryVars['where_expiry_day'] ) {
				$where .= $wpdb->prepare(
					" AND ( %d = COALESCE( DAY( $sJoinKey.expiry_date ), DAY( {$wpdb->posts}.post_date ) ) ) ",
					$aQueryVars['where_expiry_day']
				);		
			}
			
			if ( $aQueryVars['where_expiry_monthnum'] ) {
				$where .= $wpdb->prepare(
					" AND ( %d = COALESCE( MONTH( $sJoinKey.expiry_date ), MONTH( {$wpdb->posts}.post_date ) ) ) ",
					$aQueryVars['where_expiry_monthnum']
				);		
			}

			if ( $aQueryVars['where_expiry_year'] ) {
				$where .= $wpdb->prepare(
					" AND ( %d = COALESCE( YEAR( $sJoinKey.expiry_date ), YEAR( {$wpdb->posts}.post_date ) ) ) ",
					$aQueryVars['where_expiry_year']
				);		
			}
			
			
			
			if ( $aQueryVars['where_min_date'] ) {
				$where .= $wpdb->prepare(
					" AND ( CAST( COALESCE( $sJoinKey.expiry_date, $sJoinKey.start_date, {$wpdb->posts}.post_date ) AS DATE ) >= CAST( %s AS DATE ) ) ",
					$aQueryVars['where_min_date']
				);		
			}			
			
			if ( $aQueryVars['where_max_date'] ) {
				$where .= $wpdb->prepare(
					" AND ( CAST( COALESCE( $sJoinKey.start_date, $sJoinKey.expiry_date, {$wpdb->posts}.post_date ) AS DATE ) <= CAST( %s AS DATE ) ) ",
					$aQueryVars['where_max_date']
				);		
			}			
			
			if ( $aQueryVars['where_between_date'] ) {
				$where .= $wpdb->prepare(
					" AND (
						CAST( %s AS DATE ) BETWEEN 
						CAST( COALESCE( $sJoinKey.start_date, $sJoinKey.expiry_date, {$wpdb->posts}.post_date ) AS DATE ) AND 
						CAST( COALESCE( $sJoinKey.expiry_date, $sJoinKey.start_date, {$wpdb->posts}.post_date ) AS DATE )
					) ",
					$aQueryVars['where_between_date']
				);		
			}			
			
			
			
		}
		
		return $where;
	}

	//
	public static function join($join)
	{
		global $wpdb;
		$sJoinKey = self::getJoinKey();
		
		if (self::$bJoin) {
			$join .= " LEFT JOIN $wpdb->geko_expiry $sJoinKey ON {$wpdb->posts}.ID = $sJoinKey.post_id ";
		}
		
		return $join;
	}
	
	//
	public static function orderby($orderby)
	{
		global $wpdb;
		$sJoinKey = self::getJoinKey();
		
		$aGekoRawQueryVars = self::$oWpQuery->geko_raw_query_vars;
		
		if (self::$bJoin && 'start_date' == $aGekoRawQueryVars['orderby']) {
			$order = $aGekoRawQueryVars['order'];
			if ('' == $order) $order = 'DESC';
			
			$orderby = " COALESCE($sJoinKey.start_date, {$wpdb->posts}.post_date) $order ";
		}

		if (self::$bJoin && 'expiry_date' == $aGekoRawQueryVars['orderby']) {
			$order = $aGekoRawQueryVars['order'];
			if ('' == $order) $order = 'DESC';
			
			$orderby = " COALESCE($sJoinKey.expiry_date, {$wpdb->posts}.post_date) $order ";
		}
		
		return $orderby;
	}
	
	//
	public static function fields($fields)
	{
		global $wp_query, $wpdb;
		$sJoinKey = self::getJoinKey();
		
		$aQueryVars = self::$oWpQuery->query_vars;
		
		if (self::$bJoin && $aQueryVars['add_is_expired_field']) {
			$fields .= " , ($sJoinKey.expiry_date <= NOW()) AS is_expired ";
		}

		if (self::$bJoin && $aQueryVars['add_start_date_field']) {
			$fields .= " , COALESCE($sJoinKey.start_date, {$wpdb->posts}.post_date) AS start_date ";
		}

		if (self::$bJoin && $aQueryVars['add_expiry_date_field']) {
			$fields .= " , COALESCE($sJoinKey.expiry_date, {$wpdb->posts}.post_date) AS expiry_date ";
		}

		if (self::$bJoin && $aQueryVars['add_min_date_field']) {
			$fields .= " , CAST( COALESCE($sJoinKey.start_date, $sJoinKey.expiry_date, {$wpdb->posts}.post_date) AS DATE ) AS min_date ";
		}

		if (self::$bJoin && $aQueryVars['add_max_date_field']) {
			$fields .= " , CAST( COALESCE($sJoinKey.expiry_date, $sJoinKey.start_date, {$wpdb->posts}.post_date) AS DATE ) AS max_date ";
		}
		
		return $fields;
	}
	
	
	
	//
	public static function register() {
		parent::register(__CLASS__);
	}
	
	//
	public static function getJoinKey() {
		return parent::getJoinKey(__CLASS__);
	}
	
}



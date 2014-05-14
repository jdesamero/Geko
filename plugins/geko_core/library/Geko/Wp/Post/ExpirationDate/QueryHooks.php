<?php

// adds "start_date" and "expiry_date" fields to the query_posts()
class Geko_Wp_Post_ExpirationDate_QueryHooks extends Geko_Wp_Query_Hooks_Abstract
{
	protected static $bJoin = FALSE;
	
	
	
	//
	public static function where( $where ) {
		
		global $wpdb;
		
		$sJoinKey = self::getJoinKey();
		
		// determine whether the join needs to be made
		self::set_geko_raw_query_vars();
		
		$aQueryVars = self::$oWpQuery->query_vars;
		$aGekoRawQueryVars = self::$oWpQuery->geko_raw_query_vars;
		
		if (
			$aQueryVars[ 'hide_expired' ] ||
			$aQueryVars[ 'hide_unexpired' ] ||
			
			$aQueryVars[ 'add_start_date_field' ] || 
			$aQueryVars[ 'add_expiry_date_field' ] || 
			$aQueryVars[ 'add_is_expired_field' ] || 

			$aQueryVars[ 'where_start_day' ] || 
			$aQueryVars[ 'where_start_monthnum' ] || 
			$aQueryVars[ 'where_start_year' ] || 

			$aQueryVars[ 'where_expiry_day' ] || 
			$aQueryVars[ 'where_expiry_monthnum' ] || 
			$aQueryVars[ 'where_expiry_year' ] || 
			
			$aQueryVars[ 'where_min_date' ] || 
			$aQueryVars[ 'where_max_date' ] || 
			$aQueryVars[ 'where_between_date' ] ||
			
			'start_date' == $aGekoRawQueryVars[ 'orderby' ] || 
			'expiry_date' == $aGekoRawQueryVars[ 'orderby' ]
		) {
			self::$bJoin = TRUE;
		} else {
			self::$bJoin = FALSE;
		}
		
		// - - - - -
		
		if ( self::$bJoin ) {
		
			if ( $aQueryVars[ 'hide_expired' ] ) {
				$where .= sprintf( " AND ( COALESCE(%s.expiry_date, %s.post_date) > NOW() ) ", $sJoinKey, $wpdb->posts );		
			}
	
			if ( $aQueryVars[ 'hide_unexpired' ] ) {
				$where .= sprintf( " AND ( COALESCE(%s.expiry_date, %s.post_date) <= NOW() ) ", $sJoinKey, $wpdb->posts );
			}
			
			
			
			if ( $aQueryVars[ 'where_start_day' ] ) {
				$where .= $wpdb->prepare(
					sprintf( " AND ( %%d = COALESCE( DAY( %s.start_date ), DAY( %s.post_date ) ) ) ", $sJoinKey, $wpdb->posts ),
					$aQueryVars[ 'where_start_day' ]
				);
			}
			
			if ( $aQueryVars[ 'where_start_monthnum' ] ) {
				$where .= $wpdb->prepare(
					sprintf( " AND ( %%d = COALESCE( MONTH( %s.start_date ), MONTH( %s.post_date ) ) ) ", $sJoinKey, $wpdb->posts ),
					$aQueryVars[ 'where_start_monthnum' ]
				);		
			}

			if ( $aQueryVars[ 'where_start_year' ] ) {
				$where .= $wpdb->prepare(
					sprintf( " AND ( %%d = COALESCE( YEAR( %s.start_date ), YEAR( %s.post_date ) ) ) ", $sJoinKey, $wpdb->posts ),
					$aQueryVars[ 'where_start_year' ]
				);		
			}
			
			
			
			if ( $aQueryVars[ 'where_expiry_day' ] ) {
				$where .= $wpdb->prepare(
					sprintf( " AND ( %%d = COALESCE( DAY( %s.expiry_date ), DAY( %s.post_date ) ) ) ", $sJoinKey, $wpdb->posts ),
					$aQueryVars[ 'where_expiry_day' ]
				);		
			}
			
			if ( $aQueryVars[ 'where_expiry_monthnum' ] ) {
				$where .= $wpdb->prepare(
					sprintf( " AND ( %%d = COALESCE( MONTH( %s.expiry_date ), MONTH( %s.post_date ) ) ) ", $sJoinKey, $wpdb->posts ),
					$aQueryVars[ 'where_expiry_monthnum' ]
				);		
			}

			if ( $aQueryVars[ 'where_expiry_year' ] ) {
				$where .= $wpdb->prepare(
					sprintf( " AND ( %%d = COALESCE( YEAR( %s.expiry_date ), YEAR( %s.post_date ) ) ) ", $sJoinKey, $wpdb->posts ),
					$aQueryVars[ 'where_expiry_year' ]
				);
			}
			
			
			
			if ( $aQueryVars[ 'where_min_date' ] ) {
				$where .= $wpdb->prepare(
					sprintf( " AND ( CAST( COALESCE( %s.expiry_date, %s.start_date, %s.post_date ) AS DATE ) >= CAST( %%s AS DATE ) ) ", $sJoinKey, $sJoinKey, $wpdb->posts ),
					$aQueryVars[ 'where_min_date' ]
				);		
			}			
			
			if ( $aQueryVars[ 'where_max_date' ] ) {
				$where .= $wpdb->prepare(
					sprintf( " AND ( CAST( COALESCE( %s.start_date, %s.expiry_date, %s.post_date ) AS DATE ) <= CAST( %%s AS DATE ) ) ", $sJoinKey, $sJoinKey, $wpdb->posts ),
					$aQueryVars[ 'where_max_date' ]
				);		
			}
			
			if ( $aQueryVars[ 'where_between_date' ] ) {
				$where .= $wpdb->prepare(
					sprintf( " AND (
						CAST( %%s AS DATE ) BETWEEN 
						CAST( COALESCE( %s.start_date, %s.expiry_date, %s.post_date ) AS DATE ) AND 
						CAST( COALESCE( %s.expiry_date, %s.start_date, %s.post_date ) AS DATE )
					) ", $sJoinKey, $sJoinKey, $wpdb->posts, $sJoinKey, $sJoinKey, $wpdb->posts ),
					$aQueryVars[ 'where_between_date' ]
				);		
			}			
			
			
			
		}
		
		return $where;
	}
	
	
	//
	public static function join( $join ) {
		
		global $wpdb;
		
		$sJoinKey = self::getJoinKey();
		
		if ( self::$bJoin ) {
			$join .= sprintf( " LEFT JOIN %s %s ON %s.ID = %s.post_id ", $wpdb->geko_expiry, $sJoinKey, $wpdb->posts, $sJoinKey );
		}
		
		return $join;
	}
	
	
	//
	public static function orderby( $orderby ) {
		
		global $wpdb;
		
		$sJoinKey = self::getJoinKey();
		
		$aGekoRawQueryVars = self::$oWpQuery->geko_raw_query_vars;
		
		if ( self::$bJoin && ( 'start_date' == $aGekoRawQueryVars[ 'orderby' ] ) ) {
			
			$order = $aGekoRawQueryVars[ 'order' ];
			if ( '' == $order ) $order = 'DESC';
			
			$orderby = sprintf( " COALESCE(%s.start_date, %s.post_date) %s ", $sJoinKey, $wpdb->posts, $order );
		}
		
		if ( self::$bJoin && ( 'expiry_date' == $aGekoRawQueryVars[ 'orderby' ] ) ) {
			
			$order = $aGekoRawQueryVars[ 'order' ];
			if ( '' == $order ) $order = 'DESC';
			
			$orderby = sprintf( " COALESCE(%s.expiry_date, %s.post_date) %s ", $sJoinKey, $wpdb->posts, $order );
		}
		
		return $orderby;
	}
	
	
	//
	public static function fields( $fields ) {
		
		global $wp_query, $wpdb;
		
		$sJoinKey = self::getJoinKey();
		
		$aQueryVars = self::$oWpQuery->query_vars;
		
		if ( self::$bJoin && $aQueryVars[ 'add_is_expired_field' ] ) {
			$fields .= sprintf( " , (%s.expiry_date <= NOW()) AS is_expired ", $sJoinKey );
		}
		
		if ( self::$bJoin && $aQueryVars[ 'add_start_date_field' ] ) {
			$fields .= sprintf( " , COALESCE(%s.start_date, %s.post_date) AS start_date ", $sJoinKey, $wpdb->posts );
		}

		if ( self::$bJoin && $aQueryVars[ 'add_expiry_date_field' ] ) {
			$fields .= sprintf( " , COALESCE(%s.expiry_date, %s.post_date) AS expiry_date ", $sJoinKey, $wpdb->posts );
		}
		
		if ( self::$bJoin && $aQueryVars[ 'add_min_date_field' ] ) {
			$fields .= sprintf( " , CAST( COALESCE(%s.start_date, %s.expiry_date, %s.post_date) AS DATE ) AS min_date ", $sJoinKey, $sJoinKey, $wpdb->posts );
		}
		
		if ( self::$bJoin && $aQueryVars[ 'add_max_date_field' ] ) {
			$fields .= sprintf( " , CAST( COALESCE(%s.expiry_date, %s.start_date, %s.post_date) AS DATE ) AS max_date ", $sJoinKey, $sJoinKey, $wpdb->posts );
		}
		
		return $fields;
	}
	
	
	
	//
	public static function register() {
		parent::register( __CLASS__ );
	}
	
	//
	public static function getJoinKey() {
		return parent::getJoinKey( __CLASS__ );
	}
	
}



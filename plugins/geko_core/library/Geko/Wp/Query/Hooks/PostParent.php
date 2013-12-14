<?php

//
class Geko_Wp_Query_Hooks_PostParent extends Geko_Wp_Query_Hooks_Abstract
{	
	public static $bJoin = FALSE;
	
	//
	public static function where( $sWhere ) {
		
		global $wpdb;
		
		// determine whether the join needs to be made
		self::set_geko_raw_query_vars();
		
		if ( $mParentIds = self::$oWpQuery->query_vars[ 'post_parent__in' ] ) {
			
			if ( is_array( $mParentIds ) ) {
				$sParentIds = implode( ',', $mParentIds );
			} else {
				$sParentIds = $mParentIds;
			}
			
			if ( $sParentIds = trim( $sParentIds ) ) {
				$sWhere .= " AND ( {$wpdb->posts}.post_parent IN ($sParentIds) ) ";
			}
		}
		
		// where clause not manipulated
		return $sWhere;
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


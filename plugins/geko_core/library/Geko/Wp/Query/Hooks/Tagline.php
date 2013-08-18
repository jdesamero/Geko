<?php

// includes the "Tagline" custom field as part of query_posts()
class Geko_Wp_Query_Hooks_Tagline extends Geko_Wp_Query_Hooks_Abstract
{	
	public static $bJoin = FALSE;
	
	//
	public static function where($where)
	{		
		// determine whether the join needs to be made
		self::set_geko_raw_query_vars();
		
		if (self::$oWpQuery->query_vars['add_tagline_field']) {
			self::$bJoin = TRUE;
		} else {
			self::$bJoin = FALSE;
		}
		
		// - - - - -
		
		// where clause not manipulated
		return $where;
	}
	
	//
	public static function join($join)
	{
		global $wpdb;
		$sJoinKey = self::getJoinKey();
		
		if (self::$bJoin) {
			$join .= " LEFT JOIN $wpdb->postmeta $sJoinKey ON ({$wpdb->posts}.ID = $sJoinKey.post_id) AND ('Tagline' = $sJoinKey.meta_key) ";
		}
		
		return $join;
	}
	
	//
	public static function fields($fields)
	{
		$sJoinKey = self::getJoinKey();
		
		if (self::$bJoin && self::$oWpQuery->query_vars['add_tagline_field']) {
			$fields .= " , $sJoinKey.meta_value AS tagline ";
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


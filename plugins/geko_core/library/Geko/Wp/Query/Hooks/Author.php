<?php

// includes the "Author" custom field as part of query_posts()
class Geko_Wp_Query_Hooks_Author extends Geko_Wp_Query_Hooks_Abstract
{	
	protected static $bJoin = FALSE;
	
	//
	public static function where( $sWhere )
	{
		// determine whether the join needs to be made
		self::set_geko_raw_query_vars();
		
		if ( self::$oWpQuery->query_vars[ 'add_author_field' ] ) {
			self::$bJoin = TRUE;
		}
		
		// - - - - -
		
		// where clause not manipulated
		return $sWhere;
	}
	
	//
	public static function join( $sJoin )
	{
		global $wpdb;
		
		if ( self::$bJoin ) {
			$sJoinKey = self::getJoinKey();
			$sJoin .= " LEFT JOIN $wpdb->postmeta $sJoinKey ON ({$wpdb->posts}.ID = $sJoinKey.post_id) AND ('Author' = $sJoinKey.meta_key) ";
		}
		
		return $sJoin;
	}
	
	//
	public static function fields( $sFields )
	{	
		if ( self::$bJoin && self::$oWpQuery->query_vars['add_author_field'] ) {
			$sJoinKey = self::getJoinKey();
			$sFields .= " , $sJoinKey.meta_value AS author ";
		}
		
		return $sFields;
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


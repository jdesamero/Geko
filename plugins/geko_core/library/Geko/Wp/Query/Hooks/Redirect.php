<?php

// includes the "Redirect" custom field as part of query_posts()
class Geko_Wp_Query_Hooks_Redirect extends Geko_Wp_Query_Hooks_Abstract
{
	//
	public static function join( $sJoin )
	{
		global $wpdb;
		$sJoinKey = self::getJoinKey();
		
		$sJoin .= " LEFT JOIN $wpdb->postmeta $sJoinKey ON ({$wpdb->posts}.ID = $sJoinKey.post_id) AND ('Redirect' = $sJoinKey.meta_key) ";
		
		return $sJoin;
	}
	
	//
	public static function fields( $sFields )
	{		
		$sJoinKey = self::getJoinKey();
		$sFields .= " , $sJoinKey.meta_value AS redirect ";
		
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


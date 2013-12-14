<?php

//
class Geko_Wp_Query_Hooks_Comment extends Geko_Wp_Query_Hooks_Abstract
{	
	//
	public static function fields($fields)
	{
		global $wpdb;
		
		self::set_geko_raw_query_vars();
		
		$bCommentLatest = self::$oWpQuery->query_vars['add_comment_latest_date_field'];
		$bLatestActivity = self::$oWpQuery->query_vars['add_latest_activity_date_field'];
		$sOrderByField = self::$oWpQuery->geko_raw_query_vars['orderby'];
		
		if (
			( $bCommentLatest ) || 
			( $bLatestActivity ) || 
			in_array( $sOrderByField, array( 'comment_latest_date', 'latest_activity_date' ) )
		) {
			
			$fields .= " , @cld := (
				SELECT			MAX( c.comment_date )
				FROM			$wpdb->comments c
				WHERE			( c.comment_approved = 1 ) AND 
								( c.comment_post_ID = {$wpdb->posts}.ID )
			) AS comment_latest_date ";
			
			if ( $bLatestActivity || ( 'latest_activity_date' == $sOrderByField ) ) {
				$fields .= " , COALESCE( @cld, {$wpdb->posts}.post_date ) AS latest_activity_date ";
			}
			
		}
		
		return $fields;
	}
	
	//
	public static function orderby($orderby)
	{
		global $wpdb;
		
		self::set_geko_raw_query_vars();
		
		$sOrderByField = self::$oWpQuery->geko_raw_query_vars['orderby'];
		if ( in_array( $sOrderByField, array( 'comment_count', 'comment_latest_date', 'latest_activity_date' ) ) ) {
			
			$order = self::$oWpQuery->geko_raw_query_vars['order'];
			if ('' == $order) $order = 'ASC';
			
			$orderby = " $sOrderByField $order ";
		}
		
		return $orderby;
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


<?php

//
class Geko_Wp_Page_QueryHooks extends Geko_Wp_Query_Hooks_Abstract
{	
	public static $bJoin = FALSE;
	
	//
	public static function fields( $sFields ) {
		
		if ( 'page' == self::$oWpQuery->query_vars['post_type'] ) {
			$sFields .= "
				, gktmpl.meta_value AS page_template
			";
		}
		
		return $sFields;
	}
	
	//
	public static function join( $sJoin ) {
		
		global $wpdb;
		
		if ( 'page' == self::$oWpQuery->query_vars['post_type'] ) {
			$sJoin .= "
				LEFT JOIN				{$wpdb->postmeta} gktmpl
					ON					( gktmpl.post_id = {$wpdb->posts}.ID ) AND 
										( gktmpl.meta_key = '_wp_page_template' )
			";
		}
		
		return $sJoin;
	}
	
	//
	public static function where( $sWhere ) {
		
		global $wpdb;
		
		if ( 'page' == self::$oWpQuery->query_vars['post_type'] ) {

			if ( $sValue = self::$oWpQuery->query_vars['page_template'] ) {
				$sWhere .= " AND ( gktmpl.meta_value = '" . $wpdb->prepare( $sValue ) . "' ) ";
			}
			
		}
		
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



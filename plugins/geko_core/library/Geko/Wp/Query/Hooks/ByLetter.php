<?php

// allows to filter posts by letter
class Geko_Wp_Query_Hooks_ByLetter extends Geko_Wp_Query_Hooks_Abstract
{	
	//
	public static function where($where)
	{
		global $wpdb;
		
		// determine whether the join needs to be made
		self::set_geko_raw_query_vars();
		
		$sLetter = self::$oWpQuery->query_vars['filter_by_letter'];
		
		if ( '#' == $sLetter ) {
			$where .= " AND ( UPPER( SUBSTRING( {$wpdb->posts}.post_title, 1, 1 ) ) REGEXP '[0-9]' ) ";		
		} elseif ( $sLetter ) {
			$where .= " AND ( UPPER( SUBSTRING( {$wpdb->posts}.post_title, 1, 1 ) ) = '" . strtoupper( $sLetter ) . "' ) ";
		}
		
		return $where;
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


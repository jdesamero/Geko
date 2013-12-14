<?php

// allows to filter posts by letter
class Geko_Wp_Query_Hooks_Random extends Geko_Wp_Query_Hooks_Abstract
{	
	//
	public static function orderby( $sOrderby )
	{
		global $wpdb;
		$sJoinKey = self::getJoinKey();
		
		$aGekoRawQueryVars = self::$oWpQuery->geko_raw_query_vars;
		
		if ( 'random' == $aGekoRawQueryVars[ 'orderby' ] ) {
			$sOrderby = " RAND() ";
		}
		
		return $sOrderby;
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


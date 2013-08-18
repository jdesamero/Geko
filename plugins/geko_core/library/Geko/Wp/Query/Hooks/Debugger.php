<?php

// a utility class for debugging the sequence of query_post() hooks
class Geko_Wp_Query_Hooks_Debugger extends Geko_Wp_Query_Hooks_Abstract
{
	public static $iCount = 10;
	
	
	
	//
	private static function output($method, $args)
	{
		printf (
			"%s: %s; ",
			$method,
			self::$iCount++
		);
		
		/* /
		printf (
			"%s: c-%s, a-%s; ",
			$method,
			self::$iCount++,
			count($args)
		);
		/* */
	}
	
	
	
	//
	public static function where($val)
	{
		self::set_geko_raw_query_vars();
		
		/* */
		var_dump(self::$oWpQuery->query);
		//var_dump(self::$oWpQuery->query_vars);
		var_dump(self::$oWpQuery->geko_raw_query_vars);
		/* */
		
		$args = func_get_args();
		self::output(__FUNCTION__, $args);
		
		return $val;
	}	
	
	//
	public static function join($val)
	{
		$args = func_get_args();
		self::output(__FUNCTION__, $args);
		
		return $val;
	}
	
	//
	public static function where_paged($val)
	{
		$args = func_get_args();
		self::output(__FUNCTION__, $args);
		
		return $val;
	}
	
	//
	public static function groupby($val)
	{
		$args = func_get_args();
		self::output(__FUNCTION__, $args);
		
		return $val;
	}
	
	//
	public static function join_paged($val)
	{
		$args = func_get_args();
		self::output(__FUNCTION__, $args);
		
		return $val;
	}
	
	//
	public static function orderby($val)
	{
		$args = func_get_args();
		self::output(__FUNCTION__, $args);
		
		return $val;
	}
	
	//
	public static function distinct($val)
	{
		$args = func_get_args();
		self::output(__FUNCTION__, $args);
		
		return $val;
	}
	
	//
	public static function fields($val)
	{
		$args = func_get_args();
		self::output(__FUNCTION__, $args);
		
		return $val;
	}
	
	//
	public static function limits($val)
	{
		$args = func_get_args();
		self::output(__FUNCTION__, $args);
		
		return $val;
	}
	
	//
	public static function request($val)
	{
		$args = func_get_args();
		self::output(__FUNCTION__, $args);
		
		return $val;
	}
	
	//
	public static function request($val)
	{
		$args = func_get_args();
		self::output(__FUNCTION__, $args);
		
		return $val;
	}
	
	
	
	//
	public static function register()
	{
		parent::register(__CLASS__);
	}
	
}


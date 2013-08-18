<?php

//
class Geko_Wp_Language_Manage_Post_QueryHooks extends Geko_Wp_Query_Hooks_Abstract
{
	protected static $bJoin = FALSE;
	protected static $bFields = FALSE;
	protected static $sLangCode = '';
	
	//
	public static function where( $sWhere, $aParams = array() )
	{
		$q = self::$oWpQuery->query_vars;
		
		self::$sLangCode = $q[ 'lang' ];
		self::$bFields = $q[ 'add_lang_fields' ];
		
		if ( self::$sLangCode || self::$bFields ) {
			
			global $wpdb;
			
			self::$bJoin = TRUE;
			
			$sJoinKey1 = self::getJoinKey();
			
			if ( self::$sLangCode ) {
				$sWhere .= "
					AND ( ( '" . self::$sLangCode . "' = $sJoinKey1.lang_code ) OR (
						( SELECT is_default FROM $wpdb->geko_languages WHERE code = '" . self::$sLangCode . "' ) AND 
						( $sJoinKey1.lang_code IS NULL )
					) )
				";
			}
			
		} else {
			
			self::$bJoin = FALSE;
			self::$sLangCode = '';
			
		}
		
		return $sWhere;
	}
	
	//
	public static function join( $sJoin, $aParams = array() )
	{
		if ( self::$bJoin ) {
			
			global $wpdb;
			
			$sJoinKey1 = self::getJoinKey();
			$sLangCode = self::$sLangCode;
			
			$sJoin .= "
				LEFT JOIN				(
					SELECT					lgm.obj_id,
											l.code AS lang_code,
											l.title AS lang_title
					FROM					$wpdb->geko_lang_group_members lgm
					LEFT JOIN				$wpdb->geko_lang_groups lg
						ON						( lg.lgroup_id = lgm.lgroup_id )
					LEFT JOIN				$wpdb->geko_languages l
						ON						( l.lang_id = lgm.lang_id )
					WHERE					" . Geko_Wp_Options_MetaKey::getId( 'post' ) . " = lg.type_id
				)						AS $sJoinKey1
					ON					$sJoinKey1.obj_id = {$wpdb->posts}.ID
			";
		}
		
		return $sJoin;
	}
	
	//
	public static function fields( $sFields, $aParams = array() )
	{
		if ( self::$bFields ) {
			
			$sJoinKey1 = self::getJoinKey();
			
			$sFields .= " , $sJoinKey1.lang_code , $sJoinKey1.lang_title ";
			
		}
		
		return $sFields;
	}
	
	//
	public static function register( $aParams = array() ) {
		parent::register( __CLASS__, $aParams );
	}
	
	//
	public static function getJoinKey( $aParams = array() ) {
		return parent::getJoinKey( __CLASS__, $aParams );
	}
	
}



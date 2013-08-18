<?php

// generic class for adding post meta to query_posts
class Geko_Wp_Query_Hooks_PostMeta extends Geko_Wp_Query_Hooks_Abstract
{
	protected static $aMetaKeys = array();
	
	//
	public static function where( $sWhere, $aParams = array() )
	{
		$aAddMeta = self::$oWpQuery->query_vars['add_meta'];
		
		// do checks
		if (
			isset( $aParams['meta_key'] ) &&
			isset( $aParams['meta_field_name'] ) && (
				isset( $aParams['global'] ) ||
				( !isset( $aParams['global'] ) && isset( $aAddMeta[ $aParams['meta_key'] ] ) )
			)
		) {
			global $wpdb;
			
			$sJoinKey = self::getJoinKey( $aParams );
			$sMetaKey = $aParams['meta_key'];
			$aMeta = $aAddMeta[ $sMetaKey ];
			$sMetaFieldName = $aParams['meta_field_name'];
			
			if ( isset( $aMeta['empty'] ) ) {
				if ( $aMeta['empty'] ) {
					$sWhere .= " AND ( ( $sJoinKey.meta_value IS NULL ) OR ( 0 = $sJoinKey.meta_value ) OR ( '' = $sJoinKey.meta_value ) ) ";
				} else {
					$sWhere .= " AND ( ( $sJoinKey.meta_value IS NOT NULL ) OR ( 0 != $sJoinKey.meta_value ) OR ( '' != $sJoinKey.meta_value ) ) ";
				}
			}
			
			if ( isset( $aMeta['match'] ) ) {
				
				// TO DO: Add more
				$aMatchOps = array('LIKE', 'RLIKE');
								
				// check for meta key specific match op
				if ( isset( $aMeta['match_op'] ) ) {
					$sMatchOp = strtoupper( $aMeta['match_op'] );
					if ( !in_array( $sMatchOp, $aMatchOps ) ) $sMatchOp = '';	// bad op given
				}
				
				// fall back to global cast type if set
				if ( !$sMatchOp && isset( $aParams['match_op'] ) ) {
					$sMatchOp = strtoupper( $aParams['match_op'] );
					if ( !in_array( $sMatchOp, $aMatchOps ) ) $sMatchOp = '';	// bad op given
				}
				
				// defaults to equal
				$sMatchOp = ( $sMatchOp ) ? $sMatchOp : '=';
				$sMatchValue = $aMeta['match'];
				
				$sWhere .= $wpdb->prepare( " AND ( %s $sMatchOp $sJoinKey.meta_value ) ", $sMatchValue );
			}
			
			if (
				( isset( $aMeta['searchable'] ) ) && 
				( isset( self::$oWpQuery->query_vars['search_terms'] ) )
			) {
				foreach ( self::$oWpQuery->query_vars['search_terms'] as $sTerm ) {
					$sHook = "({$wpdb->posts}.post_title LIKE '%" . $wpdb->escape( $sTerm ) . "%')";
					$sAdd = " OR ( $sJoinKey.meta_value LIKE '%" . $wpdb->escape( $sTerm ) . "%' )";
					$sWhere = str_replace( $sHook, $sHook . $sAdd, $sWhere );
				}
			}
			
		}
		
		return $sWhere;
	}
	
	//
	public static function join( $sJoin, $aParams = array() )
	{
		$aAddMeta = self::$oWpQuery->query_vars['add_meta'];
		
		// do checks
		if (
			isset( $aParams['meta_key'] ) && (
				isset( $aParams['global'] ) ||
				( !isset( $aParams['global'] ) && isset( $aAddMeta[ $aParams['meta_key'] ] ) )
			)
		) {
			global $wpdb;
			
			$sJoinKey = self::getJoinKey( $aParams );
			$sMetaKey = $aParams['meta_key'];
			
			$sJoin .= " LEFT JOIN $wpdb->postmeta $sJoinKey ON ({$wpdb->posts}.ID = $sJoinKey.post_id) AND ('$sMetaKey' = $sJoinKey.meta_key) ";
		}
		
		return $sJoin;
	}
	
	//
	public static function fields( $sFields, $aParams = array() )
	{
		$aAddMeta = self::$oWpQuery->query_vars['add_meta'];
		
		// do checks
		if (
			isset( $aParams['meta_key'] ) &&
			isset( $aParams['meta_field_name'] ) && (
				isset( $aParams['global'] ) ||
				( !isset( $aParams['global'] ) && isset( $aAddMeta[ $aParams['meta_key'] ] ) )
			)
		) {
			
			$sJoinKey = self::getJoinKey( $aParams );
			$sMetaKey = $aParams['meta_key'];
			$aMeta = $aAddMeta[ $sMetaKey ];
			$sMetaFieldName = $aParams['meta_field_name'];
			
			$sCastType = '';
			
			$aCastTypes = array('BINARY', 'CHAR', 'DATE', 'DATETIME', 'DECIMAL', 'SIGNED', 'TIME', 'UNSIGNED');
			
			// check for meta key specific cast stype
			if ( isset( $aMeta['cast_type'] ) ) {
				$sCastType = strtoupper( $aMeta['cast_type'] );
				if ( !in_array( $sCastType, $aCastTypes ) ) $sCastType = '';	// bad type given
			}
			
			// fall back to global cast type if set
			if ( !$sCastType && isset( $aParams['cast_type'] ) ) {
				$sCastType = strtoupper( $aParams['cast_type'] );
				if ( !in_array( $sCastType, $aCastTypes ) ) $sCastType = '';	// bad type given
			}
			
			if ( $sCastType ) {
				$sFields .= " , CAST( $sJoinKey.meta_value AS $sCastType ) AS $sMetaFieldName ";				
			} else {
				$sFields .= " , $sJoinKey.meta_value AS $sMetaFieldName ";
			}
			
		}
		
		return $sFields;
	}
	
	//
	public static function orderby( $sOrderby, $aParams = array() )
	{
		$aAddMeta = self::$oWpQuery->query_vars['add_meta'];
		
		// do checks
		if (
			isset( $aParams['meta_key'] ) &&
			isset( $aParams['meta_field_name'] ) &&
			isset( $aAddMeta[ $aParams['meta_key'] ]['order'] )
		) {			
			$sJoinKey = self::getJoinKey( $aParams );
			
			$sMetaKey = $aParams['meta_key'];
			$aMeta = $aAddMeta[ $sMetaKey ];
			
			$sMetaFieldName = $aParams['meta_field_name'];
			$sOrder = ( 'DESC' == strtoupper( $aMeta['order'] ) ) ? 'DESC' : 'ASC';
			
			if ( $aAddMeta[ $aParams['meta_key'] ]['order_append'] ) {
				$sOrderby .= ( ( $sOrderby ) ? ' , ' : '' ) . " $sMetaFieldName $sOrder ";
			} else {
				$sOrderby = " $sMetaFieldName $sOrder ";
			}
		}
		
		return $sOrderby;
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


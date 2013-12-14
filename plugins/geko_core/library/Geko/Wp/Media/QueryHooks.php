<?php

//
class Geko_Wp_Media_QueryHooks extends Geko_Wp_Query_Hooks_Abstract
{	
	public static $bJoin = FALSE;
	
	//
	public static function fields( $sFields ) {
		
		if ( self::$oWpQuery->query_vars['post_files'] ) {
			$sFields .= "
				, gkpf.ID AS file_id
				, gkpf.post_title AS file_title
				, gkpf.post_name AS file_name
				, gkpf.post_content AS file_description
				, gkpf.post_excerpt AS file_desc_excerpt
				, gkpf.post_date AS file_created
				, gkpf.post_modified AS file_modified
				, gkpf.post_date_gmt AS file_created_gmt
				, gkpf.post_modified_gmt AS file_modified_gmt
				, gkpf.post_mime_type AS file_mime_type
				, gkpf.menu_order AS file_menu_order
				, gkia.meta_value AS file_alt_text
			";
		}
		
		return $sFields;
	}
	
	//
	public static function join( $sJoin ) {
		
		global $wpdb;
		
		if ( self::$oWpQuery->query_vars['post_files'] ) {
			$sJoin .= "
				INNER JOIN				{$wpdb->posts} gkpf
					ON					( gkpf.post_parent = {$wpdb->posts}.ID ) AND 
										( gkpf.post_type = 'attachment' )
				LEFT JOIN				{$wpdb->postmeta} gkia
					ON					( gkia.post_id = gkpf.ID ) AND 
										( gkia.meta_key = '_wp_attachment_image_alt' )
			";
		}
		
		return $sJoin;
	}
	
	//
	public static function where( $sWhere ) {
		
		global $wpdb;
		

		if ( self::$oWpQuery->query_vars['post_files'] ) {

			if ( $sValue = self::$oWpQuery->query_vars['content_like'] ) {
				$sWhere .= " AND ( gkpf.post_content LIKE '%" . $wpdb->prepare( $sValue ) . "%' ) ";
			}
	
			if ( $sValue = self::$oWpQuery->query_vars['content_not_like'] ) {
				$sWhere .= " AND ( gkpf.post_content NOT LIKE '%" . $wpdb->prepare( $sValue ) . "%' ) ";
			}
			
			if ( $sValue = self::$oWpQuery->query_vars['excerpt_like'] ) {
				$sWhere .= " AND ( gkpf.post_excerpt LIKE '%" . $wpdb->prepare( $sValue ) . "%' ) ";
			}
	
			if ( $sValue = self::$oWpQuery->query_vars['excerpt_not_like'] ) {
				$sWhere .= " AND ( gkpf.post_excerpt NOT LIKE '%" . $wpdb->prepare( $sValue ) . "%' ) ";
			}
			
			if ( $sValue = self::$oWpQuery->query_vars['images_only'] ) {
				$sWhere .= " AND ( 1 = LOCATE( 'image', gkpf.post_mime_type ) ) ";				
			}

			if ( $sValue = self::$oWpQuery->query_vars['non_images_only'] ) {
				$sWhere .= " AND ( 0 = LOCATE( 'image', gkpf.post_mime_type ) ) ";				
			}
			
			if ( self::$oWpQuery->query_vars['has_file_ids'] ) {
				
				$mValue = self::$oWpQuery->query_vars['file_ids'];
				
				if ( is_string( $mValue ) ) $aIds = explode( ',', $mValue );
				else $aIds = $mValue;
				
				if ( !is_array( $aIds ) ) $aIds = array();
				
				$sWhere .= " AND ( gkpf.ID IN ('" . implode( "','", $aIds ) . "') ) ";
				
			}
			
		}
		
		return $sWhere;
	}
	
	//
	public static function orderby( $sOrderBy ) {
		
		global $wpdb;
		
		if ( self::$oWpQuery->query_vars['post_files'] ) {
		
			self::set_geko_raw_query_vars();
			
			if ( $sOrderByField = self::$oWpQuery->geko_raw_query_vars['orderby'] ) {
				
				$order = self::$oWpQuery->geko_raw_query_vars['order'];
				if ('' == $order) $order = 'ASC';
				
				$sOrderBy = " $sOrderByField $order ";
			}
		
		}
		
		return $sOrderBy;
	}
	
	
	//
	public static function limits( $sLimits ) {
		
		global $wpdb;
		
		if (
			( self::$oWpQuery->query_vars['post_files'] ) && 
			( '' == $sLimits ) && 
			( $iLimit = intval( self::$oWpQuery->query_vars['showposts'] ) )
		) {
			$sLimits = 'LIMIT ' . $iLimit;
		}
		
		return $sLimits;
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



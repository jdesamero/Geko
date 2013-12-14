<?php

//
class Geko_Wp_Query_Hooks_Taxonomy extends Geko_Wp_Query_Hooks_Abstract
{
	//
	public static function where( $sWhere ) {
		
		global $wpdb;
		$sJoinKey = self::getJoinKey();
		
		$aQueryVars = self::$oWpQuery->query_vars;
		
		if ( $aQueryVars[ 'taxonomy' ] ) {
			
			$aTypes = $aQueryVars[ 'taxonomy' ][ 'types' ];
			$aTerms = $aQueryVars[ 'taxonomy' ][ 'terms' ];
			
			if ( !is_array( $aTypes ) ) $aTypes = array( $aTypes );
			if ( !is_array( $aTerms ) ) $aTerms = array( $aTerms );
			
			$aTypes = array_map( 'sanitize_title', $aTypes );
			$aTerms = array_map( 'sanitize_title', $aTerms );
			
			$sTypes = implode( "','", $aTypes );
			$sTerms = implode( "','", $aTerms );
			
			$sWhere .= " AND ( $wpdb->posts.ID IN (
				SELECT			tr.object_id
				FROM			$wpdb->terms t
				INNER JOIN		$wpdb->term_taxonomy tt
								ON tt.term_id = t.term_id
				INNER JOIN		$wpdb->term_relationships tr
								ON tr.term_taxonomy_id = tt.term_taxonomy_id
				WHERE			( t.slug IN ('" . $sTerms . "') ) AND 
								( tt.taxonomy IN ('" . $sTypes . "') )			
			) ) ";
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


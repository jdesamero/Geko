<?php

// static class container for functions relating to the term_taxonomy table
class Geko_Wp_TermTaxonomy
{	
	private static $bCalledInit = FALSE;
	private static $bCalledInstall = FALSE;
	
	
	//
	public static function init()
	{
		if ( !self::$bCalledInit ) {
			
			// register db functions only once
			Geko_Wp_Db::addPrefix( 'term_taxonomy_path' );
			Geko_Wp_Db::addPrefix( 'term_taxonomy_connect' );
			
			self::$bCalledInit = TRUE;
			
		}	
	}
	
	//
	public static function install( $sTaxonomy = 'category' )
	{
		if ( !self::$bCalledInstall && is_admin() ) {
			
			// perform database installations only once
			
			global $wpdb;
			
			$wpdb->show_errors();
			
			$bRes = Geko_Wp_Db::createHierarchyPathFunction(
				'term_taxonomy', 'term_id', 'parent', " AND ( taxonomy = '$sTaxonomy' ) "
			);
			
			if ( FALSE === $bRes ) $wpdb->print_error();
			
			$bRes = Geko_Wp_Db::createHierarchyConnectFunction(
				'term_taxonomy', 'term_id', 'parent', " AND ( taxonomy = '$sTaxonomy' ) "
			);
			
			if ( FALSE === $bRes ) $wpdb->print_error();
			
			$wpdb->hide_errors();
			
			self::$bCalledInstall = TRUE;
			
		}	
	}	
	
}



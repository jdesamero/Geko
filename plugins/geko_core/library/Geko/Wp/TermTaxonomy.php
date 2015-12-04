<?php
/*
 * "geko_core/library/Geko/Wp/TermTaxonomy.php"
 * https://github.com/jdesamero/Geko
 *
 * Copyright (c) 2013 Joel Desamero.
 * Licensed under the MIT license.
 *
 * static class container for functions relating to the term_taxonomy table
 */

//
class Geko_Wp_TermTaxonomy
{	
	private static $bCalledInstall = FALSE;
	
	
	//
	public static function install( $sTaxonomy = 'category' ) {
		
		if ( !self::$bCalledInstall && is_admin() ) {
			
			// perform database installations only once
			
			// $wpdb->show_errors();
			
			$bRes = Geko_Wp_Db::createHierarchyPathFunction(
				'term_taxonomy', 'term_id', 'parent', " AND ( taxonomy = '$sTaxonomy' ) "
			);
			
			// if ( FALSE === $bRes ) $wpdb->print_error();
			
			$bRes = Geko_Wp_Db::createHierarchyConnectFunction(
				'term_taxonomy', 'term_id', 'parent', " AND ( taxonomy = '$sTaxonomy' ) "
			);
			
			// if ( FALSE === $bRes ) $wpdb->print_error();
			
			// $wpdb->hide_errors();
			
			self::$bCalledInstall = TRUE;
			
		}	
	}	
	
}



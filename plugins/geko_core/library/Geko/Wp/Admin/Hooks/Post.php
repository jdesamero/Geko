<?php
/*
 * "geko_core/library/Geko/Wp/Admin/Hooks/Post.php"
 * https://github.com/jdesamero/Geko
 *
 * Copyright (c) 2013 Joel Desamero.
 * Licensed under the MIT license.
 */

//
class Geko_Wp_Admin_Hooks_Post extends Geko_Wp_Admin_Hooks_PluginAbstract
{
	
	
	//
	public function getStates() {
		
		$oUrl = Geko_Uri::getGlobal();
		$sUrlPath = $oUrl->getPath();
		
		$sPostType = $oUrl->getVar( 'post_type' );
		$aRes = FALSE;
		
		if (
			( FALSE !== strpos( $sUrlPath, '/wp-admin/edit.php' ) ) && 
			( 'page' != $sPostType )
		) {
			
			// list posts
			$aRes = array( 'post', 'post_list' );
		
		} elseif (
			( FALSE !== strpos( $sUrlPath, '/wp-admin/post-new.php' ) ) && 
			( 'page' != $sPostType )			
		) {
			
			// add post
			$aRes = array( 'post', 'post_add' );
			
		} elseif ( FALSE !== strpos( $sUrlPath, '/wp-admin/post.php' ) ) {
			
			// edit post
			if ( $iPostId = $oUrl->getVar( 'post' ) ) {
				
				$sPostType = get_post_type( $iPostId );
								
				if ( 'page' != $sPostType ) {
					$aRes = array( 'post', 'post_edit' );
				}
			}
			
		}
		
		
		$this->setValue( 'post_type', $sPostType );
		
		return $aRes;
	}
	
	//
	public function applyFilters( $sContent, $sState ) {
		
		$sPostType = $this->getValue( 'post_type' );
		
		if ( ( 'post_edit' == $sState ) || ( 'post_add' == $sState ) ) {
			
			$aTx = get_object_taxonomies( $sPostType );
			
			foreach ( $aTx as $sTaxonomy ) {
				$sContent = $this->replace(
					$sContent,
					'admin_post_categories_meta_box_pq',
					array( array( '<div', sprintf( ' id="%sdiv"', $sTaxonomy ) ), '</div>' )
				);
			}
			
		}
		
		return $sContent;
	}
}



<?php

//
class Geko_Wp_Admin_Hooks_Post extends Geko_Wp_Admin_Hooks_PluginAbstract
{
	//
	public function getStates() {
		
		$oUrl = Geko_Uri::getGlobal();
		$sUrlPath = $oUrl->getPath();
		
		if (
			( FALSE !== strpos( $sUrlPath, '/wp-admin/edit.php' ) ) && 
			( 'page' != $oUrl->getVar( 'post_type' ) )
		) {
			
			// list posts
			return array( 'post', 'post_list' );
		
		} elseif (
			( FALSE !== strpos( $sUrlPath, '/wp-admin/post-new.php' ) ) && 
			( 'page' != $oUrl->getVar( 'post_type' ) )			
		) {
			
			// add post
			return array( 'post', 'post_add' );
			
		} elseif ( FALSE !== strpos( $sUrlPath, '/wp-admin/post.php' ) ) {
			
			// edit post
			if ( $iPostId = $oUrl->getVar( 'post' ) ) {
				$oPost = new Geko_Wp_Post( $iPostId );
				if ( 'post' == $oPost->getPostType() ) {
					return array( 'post', 'post_edit' );
				}
			}
			
		}
		
		return FALSE;
	}
	
	//
	public function applyFilters( $sContent, $sState ) {
		
		if ( ( 'post_edit' == $sState ) || ( 'post_add' == $sState ) ) {
			
			$sContent = $this->replace(
				$sContent,
				'admin_post_categories_meta_box_pq',
				array( array( '<div', ' id="categorydiv"' ), '</div>' )
			);
			
		}
		
		return $sContent;
	}
}



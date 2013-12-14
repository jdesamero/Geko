<?php

//
class Geko_Wp_Admin_Hooks_Page extends Geko_Wp_Admin_Hooks_PluginAbstract
{
	//
	public function getStates() {
		
		$oUrl = Geko_Uri::getGlobal();
		$sUrlPath = $oUrl->getPath();
		
		if (
			( FALSE !== strpos( $sUrlPath, '/wp-admin/edit-pages.php' ) ) || 
			(
				( FALSE !== strpos( $sUrlPath, '/wp-admin/edit.php' ) ) && 
				( 'page' == $oUrl->getVar( 'post_type' ) )				
			)
		) {
			
			// list pages
			return array( 'page', 'page_list' );
			
		} elseif (
			( FALSE !== strpos( $sUrlPath, '/wp-admin/page-new.php' ) ) || 
			(
				( FALSE !== strpos( $sUrlPath, '/wp-admin/post-new.php' ) ) && 
				( 'page' == $oUrl->getVar( 'post_type' ) )				
			)
		) {
			
			// add page
			return array( 'page', 'page_add' );
			
		} elseif ( FALSE !== strpos( $sUrlPath, '/wp-admin/page.php' ) ) {
			
			// edit page, < v3.0
			return array( 'page', 'page_edit' );
			
		} elseif ( FALSE !== strpos( $sUrlPath, '/wp-admin/post.php' ) ) {
			
			// edit page, >= 3.0
			if ( $iPostId = $oUrl->getVar( 'post' ) ) {
				$oPost = new Geko_Wp_Post( $iPostId );
				if ( 'page' == $oPost->getPostType() ) {
					return array( 'page', 'page_edit' );
				}
			}
		}
		
		return FALSE;
	}
	
	//
	public function applyFilters( $sContent, $sState ) {
		
		if ( ( 'page_edit' == $sState ) || ( 'page_add' == $sState ) ) {
			
			$sContent = $this->replace(
				$sContent,
				'admin_page_template_select_pq',
				'/<select[^>]*?id="page_template".+?<\/select>/s'
			);
			
			$sContent = $this->replace(
				$sContent,
				'admin_page_attributes_pq',
				array( array( '<div', ' id="pageparentdiv"' ), '</div>' )
			);
			
		}
		
		return $sContent;
	}
}




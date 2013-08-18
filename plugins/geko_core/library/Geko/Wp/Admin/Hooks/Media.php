<?php

//
class Geko_Wp_Admin_Hooks_Media extends Geko_Wp_Admin_Hooks_PluginAbstract
{
	//
	public function getStates() {
		
		$oUrl = Geko_Uri::getGlobal();
		$sUrlPath = $oUrl->getPath();
		
		if ( FALSE !== strpos( $sUrlPath, '/wp-admin/upload.php' ) ) {
			
			// list media
			return array( 'media', 'media_list' );
			
		} elseif ( FALSE !== strpos( $sUrlPath, '/wp-admin/media-new.php' ) ) {
			
			// add media
			return array( 'media', 'media_add' );
			
		} elseif ( FALSE !== strpos( $sUrlPath, '/wp-admin/media.php' ) ) {
			
			// edit media
			return array( 'media', 'media_edit' );
			
		}
		
		return FALSE;
	}


	//
	public function applyFilters( $sContent, $sState )
	{
		if ( 'media_edit' == $sState ) {

			$sContent = $this->replace(
				$sContent,
				'admin_media_edit_fields_pq',
				'/<form[^>]*?class="media-upload-form" id="media-single-form".+?<\/form>/s'
			);
			
		}
		
		return $sContent;
	}
	
}



<?php

//
class Geko_Wp_Admin_Hooks_Themes extends Geko_Wp_Admin_Hooks_PluginAbstract
{
	//
	public function getStates() {
		
		$oUrl = Geko_Uri::getGlobal();
		$sUrlPath = $oUrl->getPath();
		
		// admin
		if ( FALSE !== strpos( $sUrlPath, '/wp-admin/themes.php' ) ) {
			return array( 'themes' );
		}
		
		return FALSE;
	}
	
}


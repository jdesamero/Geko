<?php

//
class Geko_Wp_Admin_Hooks_Admin extends Geko_Wp_Admin_Hooks_PluginAbstract
{
	//
	public function getStates() {
		
		$oUrl = Geko_Uri::getGlobal();
		$sUrlPath = $oUrl->getPath();
		
		// admin
		if ( FALSE !== strpos( $sUrlPath, '/wp-admin/admin.php' ) ) {
			return array( 'admin' );
		}
		
		return FALSE;
	}
	
}


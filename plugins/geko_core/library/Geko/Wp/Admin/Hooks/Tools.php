<?php

//
class Geko_Wp_Admin_Hooks_Tools extends Geko_Wp_Admin_Hooks_PluginAbstract
{
	//
	public function getStates() {
		
		$oUrl = Geko_Uri::getGlobal();
		$sUrlPath = $oUrl->getPath();
		
		// admin
		if ( FALSE !== strpos( $sUrlPath, '/wp-admin/tools.php' ) ) {
			return array( 'tools' );
		}
		
		return FALSE;
	}
	
}


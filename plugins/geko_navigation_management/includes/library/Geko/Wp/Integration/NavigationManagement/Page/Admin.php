<?php

//
class Geko_Wp_Integration_NavigationManagement_Page_Admin
	extends Geko_Wp_NavigationManagement_Page_Admin
{
	
	//
	public function isCurrentUri() {
		
		$oCommon = Geko_Integration_Common::getInstance();
		if ( 'wp-admin' == $oCommon->getAppKey() ) {
			return parent::isCurrentUri();
		} else {
			return FALSE;
		}
		
	}

}



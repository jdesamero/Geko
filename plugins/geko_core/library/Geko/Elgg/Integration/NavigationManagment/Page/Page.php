<?php

class Geko_Elgg_Integration_NavigationManagement_Page_Page
	extends Geko_Elgg_NavigationManagement_Page_Page
{
	
	//
	public function getCurrentUser()
	{
		$oCommon = Geko_Integration_Common::getInstance();
		return $oCommon->get('user_login');
	}
	
	//
	public function isCurrentUri() {
		
		$oCommon = Geko_Integration_Common::getInstance();
		if ( 'elgg' == $oCommon->getAppCode() ) {
			return parent::isCurrentUri();
		} else {
			return FALSE;
		}
		
	}
	
}





<?php
/*
 * "geko_navigation_management/includes/library/Geko/Wp/NavigationManagement/Integration/Page/Admin.php"
 * https://github.com/jdesamero/Geko
 *
 * Copyright (c) 2013 Joel Desamero.
 * Licensed under the MIT license.
 */

//
class Geko_Wp_NavigationManagement_Integration_Page_Admin
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



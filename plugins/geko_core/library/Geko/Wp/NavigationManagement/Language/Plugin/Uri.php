<?php
/*
 * "geko_core/library/Geko/Wp/NavigationManagement/Language/Plugin/Uri.php"
 * https://github.com/jdesamero/Geko
 *
 * Copyright (c) 2013 Joel Desamero.
 * Licensed under the MIT license.
 */

//
class Geko_Wp_NavigationManagement_Language_Plugin_Uri
	extends Geko_Wp_NavigationManagement_Language_Plugin
{
	protected $_sNavigationPageClass = 'Geko_Navigation_Page_Uri';
	
	//
	public function reconcileSave( $aParam, $aOld ) {
		
		if ( $this->_sNavigationPageClass == $aParam['type'] ) {
			if ( $aOld['uri'] ) $aParam['uri'] = $aOld['uri'];
		}
		
		return $aParam;
	}
		
}



<?php
/*
 * "geko_core/library/Geko/Wp/NavigationManagement/Page/Home.php"
 * https://github.com/jdesamero/Geko
 *
 * Copyright (c) 2013 Joel Desamero.
 * Licensed under the MIT license.
 */

//
class Geko_Wp_NavigationManagement_Page_Home
	extends Geko_Navigation_Page_ImplicitLabelAbstract
{
	
	//// object methods
	
	//
	public function getHref() {
		return Geko_Wp::getHomepageUrl( __CLASS__ );
	}
	
	//
	public function getImplicitLabel() {
		return Geko_Wp::getHomepageTitle( __CLASS__ );
	}
	
	//
	public function isActive( $recursive = FALSE ) {
		
		if ( $this->_inactive ) {
			$this->_active = FALSE;
		} else {
			$this->_active = Geko_Wp::isHome( __CLASS__ );
		}
		
		return parent::isActive( $recursive );
	}
	
	
}


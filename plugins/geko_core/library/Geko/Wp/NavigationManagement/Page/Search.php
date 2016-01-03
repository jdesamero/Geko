<?php
/*
 * "geko_core/library/Geko/Wp/NavigationManagement/Page/Search.php"
 * https://github.com/jdesamero/Geko
 *
 * Copyright (c) 2013 Joel Desamero.
 * Licensed under the MIT license.
 */

//
class Geko_Wp_NavigationManagement_Page_Search
	extends Geko_Navigation_Page_ImplicitLabelAbstract
{
	
    //// object methods
    
    //
    public function getHref() {
    	// TO DO: implement properly
		return Geko_Wp::getHomepageUrl( __CLASS__ );
    }
    
    //
	public function getImplicitLabel() {
    	// TO DO: implement properly
		// return Geko_Wp::getHomepageTitle( __CLASS__ );
		return 'Search';
	}
	
    //
    public function isActive( $recursive = FALSE ) {
    	
		if ( $this->_inactive ) {
			$this->_active = FALSE;
		} else {
			$this->_active = is_search();
		}
		
		return parent::isActive( $recursive );
	}
	
	
}


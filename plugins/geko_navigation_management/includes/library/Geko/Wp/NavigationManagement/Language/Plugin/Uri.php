<?php

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



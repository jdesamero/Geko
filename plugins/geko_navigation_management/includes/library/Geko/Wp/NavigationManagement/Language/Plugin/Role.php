<?php

//
class Geko_Wp_NavigationManagement_Language_Plugin_Role
	extends Geko_Wp_NavigationManagement_Language_Plugin
{
	protected $_sNavigationPageClass = 'Geko_Wp_NavigationManagement_Page_Role';
	
	//
	public function init() {
		
		parent::init();
		
		add_filter( $this->_sNavigationPageClass . '::getHref', array( $this, 'getHref' ), 10, 2 );
		
		return $this;
	}
	
	//
	public function getHref( $sUrl, $oPageManager ) {
		
		$oResolver = Geko_Wp_Language_Resolver::getInstance();
		
		if ( $sLangCode = $oResolver->getCurLang( FALSE ) ) {
			$oUrl = new Geko_Uri( $sUrl );
			$oUrl->setVar( 'lang', $sLangCode );
			return strval( $oUrl );
		}
		
		return $sUrl;
	}
	
}



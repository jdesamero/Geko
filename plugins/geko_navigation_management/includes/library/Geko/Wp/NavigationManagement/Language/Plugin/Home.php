<?php

//
class Geko_Wp_NavigationManagement_Language_Plugin_Home
	extends Geko_Wp_NavigationManagement_Language_Plugin
{
	protected $_sNavigationPageClass = 'Geko_Wp_NavigationManagement_Page_Home';
	
	//
	public function init() {
		
		parent::init();
		
		add_filter( 'Geko_Wp::getHomepageId', array( $this, 'getHomepageId' ), 10, 2 );
		add_filter( 'Geko_Wp::getHomepageUrl', array( $this, 'getHomepageUrl' ), 10, 2 );
		
		return $this;
	}
	
	//
	public function getHomepageId( $iPageId, $sInvokerClass ) {
		
		if (
			( $this->_sNavigationPageManagerClass == $sInvokerClass ) && 
			( $oLangMgm = Geko_Wp_NavigationManagement_Language::getInstance() ) && 
			( $iLangId = $oLangMgm->getLangId() )
		) {
			$aParams = array(
				'sibling_id' => $iPageId,
				'lang_id' => $iLangId,
				'type' => 'post'
			);
			
			$oSib = Geko_Wp_Language_Member::getOne( $aParams, FALSE );
			if ( $oSib->isValid() ) return $oSib->getObjId();
		}
		
		return $iPageId;
	}
	
	//
	public function getHomepageUrl( $sUrl, $sInvokerClass ) {
		
		if ( $this->_sNavigationPageManagerClass == $sInvokerClass ) {
		
			$oResolver = Geko_Wp_Language_Resolver::getInstance();
			
			if ( $sLangCode = $oResolver->getCurLang( FALSE ) ) {
				$oUrl = new Geko_Uri( $sUrl );
				$oUrl->setVar( 'lang', $sLangCode );
				return strval( $oUrl );
			}
		
		}
		
		return $sUrl;
	}
	
}



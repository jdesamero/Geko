<?php
/*
 * "geko_core/library/Geko/Wp/NavigationManagement/Language/Plugin.php"
 * https://github.com/jdesamero/Geko
 *
 * Copyright (c) 2013 Joel Desamero.
 * Licensed under the MIT license.
 */

//
class Geko_Wp_NavigationManagement_Language_Plugin extends Geko_Singleton_Abstract
{
	
	protected $_sNavigationPageClass = '';
	protected $_sNavigationPageManagerClass = '';
	
	protected $_oLangMgm = NULL;
	protected $_oLangRslv = NULL;
	
	
	
	//
	public function init() {
		
		$this->_sNavigationPageManagerClass = Geko_Navigation_PageManager::resolvePageManagerClass(
			$this->_sNavigationPageClass, $this->_sNavigationPageManagerClass
		);
		
		$this->_oLangMgm = Geko_Wp_Language_Manage::getInstance();
		$this->_oLangRslv = Geko_Wp_Language_Resolver::getInstance();
		
		return $this;
	}
	
	//
	public function getSiblingQueryCond( $aSibParams, $aFlat ) {
		return $aSibParams;
	}
	
	//
	public function rebuildParams( $aParam, $aSibsFmt, $sLang ) {
		return $aParam;
	}
	
	//
	public function reconcileSave( $aParam, $aOld ) {
		return $aParam;
	}
	
	//
	public function modPageManager( $oPageManager ) {
		
		$oPlugin = $oPageManager->getPlugin( $this->_sNavigationPageClass );
		
		if ( $oPlugin ) {
			$oPlugin->setJsOption( array( 'disable_params' => TRUE ) );
		}
		
		return $oPageManager;
	}
	
	//
	public function modQueryParams( $aParams ) {
		$oLang = Geko_Wp_NavigationManagement_Language::getInstance();
		if ( $oLang->isDefLang() ) {
			$aParams[ 'lang' ] = $oLang->getLangCode();
		}
		return $aParams;
	}
	
	//
	public function getNavItems() {
		return array();
	}
	
}



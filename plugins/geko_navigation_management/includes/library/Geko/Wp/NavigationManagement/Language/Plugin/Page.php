<?php

//
class Geko_Wp_NavigationManagement_Language_Plugin_Page
	extends Geko_Wp_NavigationManagement_Language_Plugin
{
	protected $_sNavigationPageClass = 'Geko_Wp_NavigationManagement_Page_Page';
	
	//
	public function init() {
		
		parent::init();
		
		add_filter( 'admin_geko_wp_nav_pg_query_params', array( $this, 'modQueryParams' ) );
		
		return $this;
	}
	
	//
	public function getSiblingQueryCond( $aSibParams, $aFlat ) {
		
		$aPageIds = array();
		
		foreach ( $aFlat as $aParam ) {
			if ( $this->_sNavigationPageClass == $aParam['type'] ) {
				$aPageIds[] = $aParam['page_id'];
			}
		}
		
		$aSibParams['filter'][] = array(
			'obj_id' => $aPageIds,
			'type' => 'post'
		);
		
		return $aSibParams;		
	}
		
	//
	public function rebuildParams( $aParam, $aSibsFmt, $sLang ) {
		
		if ( $this->_sNavigationPageClass == $aParam['type'] ) {
			if ( $iPageId = $aSibsFmt['post'][ $aParam['page_id'] ][ $sLang ] ) {
				$aParam['page_id'] = $iPageId;
			}
		}
		
		return $aParam;
	}
	
	//
	public function getNavItems() {
		
		$aNavSpecific = array();
		
		if ( ( is_single() || is_page() ) && ( !Geko_Wp::isHome() ) ) {
			
			$oPost = new Geko_Wp_Post();
			$aParams = array(
				'type' => 'post',
				'sibling_id' => $oPost->getId()
			);
			
			$aSibs = new Geko_Wp_Language_Member_Query( $aParams );
			
			foreach ( $aSibs as $oSib ) {
				$aNavSpecific[ $oSib->getLangId() ] = array(
					'type' => $this->_sNavigationPageClass,
					'page_id' => $oSib->getObjId(),
					'active' => TRUE
				);
			}
			
		}
		
		return $aNavSpecific;
	}

	
}




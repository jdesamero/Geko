<?php

//
class Geko_Wp_NavigationManagement_Language_Plugin_Category
	extends Geko_Wp_NavigationManagement_Language_Plugin
{
	protected $_sNavigationPageClass = 'Geko_Wp_NavigationManagement_Page_Category';

	//
	public function init() {
		
		parent::init();
		
		add_filter( 'admin_geko_wp_nav_cat_query_params', array( $this, 'modQueryParams' ) );
		
		return $this;
	}
	
	//
	public function getSiblingQueryCond( $aSibParams, $aFlat ) {
		
		$aCatIds = array();
		
		foreach ( $aFlat as $aParam ) {
			if ( $this->_sNavigationPageClass == $aParam[ 'type' ] ) {
				$aCatIds[] = $aParam[ 'cat_id' ];
			}
		}
		
		$aSibParams[ 'filter' ][] = array(
			'obj_id' => $aCatIds,
			'type' => 'category'
		);
		
		return $aSibParams;
		
	}
		
	//
	public function rebuildParams( $aParam, $aSibsFmt, $sLang ) {
		
		if ( $this->_sNavigationPageClass == $aParam[ 'type' ] ) {
			if ( $iCatId = $aSibsFmt[ 'category' ][ $aParam[ 'cat_id' ] ][ $sLang ] ) {
				$aParam[ 'cat_id' ] = $iCatId;
			}
		}
		
		return $aParam;
	}
	
	//
	public function getNavItems() {
		
		$aNavSpecific = array();
		
		if ( is_category() ) {
			
			$oCat = new Geko_Wp_Category();
			$aParams = array(
				'type' => 'category',
				'sibling_id' => $oCat->getId()
			);

			$aSibs = new Geko_Wp_Language_Member_Query( $aParams );

			foreach ( $aSibs as $oSib ) {
				$aNavSpecific[ $oSib->getLangId() ] = array(
					'type' => $this->_sNavigationPageClass,
					'cat_id' => $oSib->getObjId()
				);
			}
			
		}
		
		return $aNavSpecific;
	}

	
}



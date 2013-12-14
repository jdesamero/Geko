<?php

//
class Geko_Wp_NavigationManagement_Language_Plugin_Post
	extends Geko_Wp_NavigationManagement_Language_Plugin
{
	protected $_sNavigationPageClass = 'Geko_Wp_NavigationManagement_Page_Post';
	
	protected $TYPE_CAT;
	protected $TYPE_AUTH;
	
	//
	public function init() {
		
		parent::init();
		
		add_filter( 'admin_geko_wp_nav_post_query_params', array( $this, 'modQueryParams' ) );
		
		// shorthand
		$this->TYPE_CAT = Geko_Wp_NavigationManagement_PageManager_Post::TYPE_CAT;
		$this->TYPE_AUTH = Geko_Wp_NavigationManagement_PageManager_Post::TYPE_AUTH;
		
		return $this;
	}
	
	//
	public function getSiblingQueryCond( $aSibParams, $aFlat ) {
		
		$aCatIds = array();
		
		foreach ( $aFlat as $aParam ) {
			if ( $this->_sNavigationPageClass == $aParam['type'] ) {
				if ( $this->TYPE_CAT == $aParam['post_type_id'] ) {
					$aCatIds[] = $aParam['cat_id'];
				}
			}
		}
		
		$aSibParams['filter'][] = array(
			'obj_id' => $aCatIds,
			'type' => 'category'
		);
		
		return $aSibParams;		
	}
		
	//
	public function rebuildParams( $aParam, $aSibsFmt, $sLang ) {
		
		if ( $this->_sNavigationPageClass == $aParam['type'] ) {
			if (
				( $iCatId = $aSibsFmt['category'][ $aParam['cat_id'] ][ $sLang ] ) && 
				( $this->TYPE_CAT == $aParam['post_type_id'] )
			) {
				$aParam['cat_id'] = $iCatId;
			}
		}
		
		return $aParam;
	}

}



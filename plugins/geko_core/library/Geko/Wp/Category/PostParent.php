<?php

//
class Geko_Wp_Category_PostParent extends Geko_Wp_Category_Meta
{
	
	//
	protected $_aCurrentCat = array();
	
	protected $_sCatVarName = 'gwcpp_catid';
	protected $_aEntities = array();
	
	
	//// configure
	
	//
	public function setCatVarName( $sCatVarName ) {
		$this->_sCatVarName = $sCatVarName;
		return $this;
	}

	//
	public function getCatVarName() {
		return $this->_sCatVarName;
	}
	
	
	
	//
	public function registerEntity() {
		$aArgs = func_get_args();
		$this->_aEntities = array_merge( $this->_aEntities, $aArgs );
		return $this;
	}
	
	
	//// init
	
	//
	public function addTheme() {
		
		parent::addTheme();
		
		foreach ( $this->_aEntities as $sEntity ) {
			add_filter( $sEntity . '::getPermalink', array( $this, 'addQueryVar' ), 10, 2 );
			add_filter( $sEntity . '::getCategory', array( $this, 'getPostCategory' ), 10, 2 );
		}
		
		add_filter( 'Geko_Wp_NavigationManagement_Page_Post::isCurrentPost::single', array( $this, 'isNavCurrentPost' ), 10, 4 );
		
		return $this;
	}
	
	
	//// hooks
	
	// add the query var for the parent category id
	public function addQueryVar( $sPermalink, $oPost ) {
		
		if ( $oQuery = $oPost->getParentQuery() ) {

			$aParams = $oQuery->getParams();
			$sPostParentParamKey = '_post_parent_cat_id';
			
			// set this once
			if ( !isset( $aParams[ $sPostParentParamKey ] ) ) {
				
				if ( $sCatPath = $aParams[ 'category_name' ] ) {
					if ( $oCat = get_category_by_path( $sCatPath ) ) {
						$iCatId = $oCat->term_id;
					} else {
						$iCatId = Geko_Wp_Category::get_ID( $sCatPath );
					}
				} else {
					$iCatId = $aParams[ 'cat' ];
				}
				
				if ( !preg_match( '/^[0-9]+$/', $iCatId ) ) $iCatId = FALSE;
				
				$oQuery->setParam( $sPostParentParamKey, $iCatId );
				$aParams = $oQuery->getParams();
				
			}

			if ( $iCatId = $aParams[ $sPostParentParamKey ] ) {
			
				$oUrl = new Geko_Uri( $sPermalink );
				$oUrl->setVar( $this->_sCatVarName, $iCatId );
				
				return strval( $oUrl );
			}
			
		}
				
		return $sPermalink;
	}
	
	
	//
	public function isNavCurrentPost( $bMatch, $iPostTypeId, $iMatchId, $oPost ) {
		
		if (
			( Geko_Wp_NavigationManagement_PageManager_Post::TYPE_CAT == $iPostTypeId ) && 
			( $iCatId = $_GET[ $this->_sCatVarName ] )
		) {
			return ( $iMatchId == $iCatId );
		}
		
		return $bMatch;
	}
	
	//
	public function getPostCategory( $oCat, $oPost ) {
		
		if ( $iCatId = $_GET[ $this->_sCatVarName ] ) {
			
			// overly complicated and dumb, but correct
			$sCategoryEntityClass = $oPost->getCategoryEntityClass();
			
			if ( !$this->_aCurrentCat[ $sCategoryEntityClass ] ) {
				$this->_aCurrentCat[ $sCategoryEntityClass ] = new $sCategoryEntityClass( $iCatId );
			}
			
			return $this->_aCurrentCat[ $sCategoryEntityClass ];
		}
		
		return $oCat;
	}
	
}



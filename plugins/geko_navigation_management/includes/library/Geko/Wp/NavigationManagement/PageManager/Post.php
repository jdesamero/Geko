<?php

//
class Geko_Wp_NavigationManagement_PageManager_Post
	extends Geko_Navigation_PageManager_ImplicitLabelAbstract
{
	const TYPE_CAT = 1;
	const TYPE_AUTH = 2;
	
	
	//
	protected $aPostTypesNorm = array();
	protected $aCatsNorm = array();
	protected $aAuthorsNorm = array();
	
	
	//
	public function init() {
		
		//// post types, normalized
		
		$this->setPostTypesNorm( array(
			self::TYPE_CAT => array( 'slug' => 'category', 'title' => 'Category' ),
			self::TYPE_AUTH => array( 'slug' => 'author', 'title' => 'Author' )
		) );
		
		//// categories normalized
		
		$aCatsNorm = array();
		
		$aParams = array( 'hide_empty' => FALSE );
		$aParams = apply_filters( 'admin_geko_wp_nav_post_query_params', $aParams, 'category' );
		
		$aCats = new Geko_Wp_Category_Query( $aParams, FALSE );
		
		foreach ( $aCats as $oCat ) {
			$aCatsNorm[ $oCat->getId() ] = array(
				'title' => $oCat->getTheTitle(),
				'link' => $oCat->getUrl()
			);
		}
		
		$this->setCatsNorm( $aCatsNorm );
		
		//// authors normalized
		
		$aAuthorsNorm = array();
		
		$aParams = array();
		$aParams = apply_filters( 'admin_geko_wp_nav_post_query_params', $aParams, 'author' );
		
		$aAuthors = new Geko_Wp_Author_Query( $aParams, FALSE );
		
		foreach ( $aAuthors as $oAuthor ) {
			$aAuthorsNorm[ $oAuthor->getId() ] = array(
				'title' => $oAuthor->getTheTitle(),
				'link' => $oAuthor->getUrl()
			);
		}
		
		$this->setAuthorsNorm( $aAuthorsNorm );
		
	}

	//
	public function setPostTypesNorm( $aPostTypesNorm ) {
		$this->aPostTypesNorm = $aPostTypesNorm;
		return $this;
	}
	
	//
	public function setCatsNorm( $aCatsNorm ) {
		$this->aCatsNorm = $aCatsNorm;
		return $this;
	}
	
	//
	public function setAuthorsNorm( $aAuthorsNorm ) {
		$this->aAuthorsNorm = $aAuthorsNorm;
		return $this;
	}
	
	
	
	//
	public function getDefaultParams() {
		
		$aParams = parent::getDefaultParams();
		$aParams['post_type_id'] = key( $this->aPostTypesNorm );
		$aParams['cat_id'] = key( $this->aCatsNorm );
		$aParams['author_id'] = key( $this->aAuthorsNorm );
		$aParams['hide'] = TRUE;
		
		return $aParams;
	}
	
	
	
	//
	public function getManagementData()
	{	
		$aData = parent::getManagementData();
		$aData['post_types'] = $this->aPostTypesNorm;
		$aData['cat_params'] = $this->aCatsNorm;
		$aData['author_params'] = $this->aAuthorsNorm;
		
		return $aData;
	}
	
	
	
	
	
	//
	public function outputStyle() {
		?>
		.type-##type## { background-color: lavender; border: dotted 1px indigo; }
		<?php
	}
	
	
	
	//
	public function outputHtml()
	{
		?>
		<label for="##nvpfx_type##post_type_id">Post Attribute to Match</label>
		<select name="##nvpfx_type##post_type_id" id="##nvpfx_type##post_type_id" class="text ui-widget-content ui-corner-all"></select>		
		
		<label for="##nvpfx_type##cat_id">Active on Matching Category</label>
		<select name="##nvpfx_type##cat_id" id="##nvpfx_type##cat_id" class="text ui-widget-content ui-corner-all"></select>		
		
		<label for="##nvpfx_type##author_id">Active on Matching Author</label>
		<select name="##nvpfx_type##author_id" id="##nvpfx_type##author_id" class="text ui-widget-content ui-corner-all"></select>
		<?php
	}
	
	
	//
    public static function getDescription() {
    	return 'Wordpress Post';
    }
    
}


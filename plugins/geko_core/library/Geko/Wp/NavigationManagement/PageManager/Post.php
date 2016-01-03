<?php
/*
 * "geko_core/library/Geko/Wp/NavigationManagement/PageManager/Post.php"
 * https://github.com/jdesamero/Geko
 *
 * Copyright (c) 2013 Joel Desamero.
 * Licensed under the MIT license.
 */

//
class Geko_Wp_NavigationManagement_PageManager_Post
	extends Geko_Navigation_PageManager_ImplicitLabelAbstract
{
	const TYPE_CAT = 1;
	const TYPE_AUTH = 2;
	const TYPE_CPT = 3;
	
	
	
	//
	protected $_aPostTypeParams = array();
	protected $_aCatParams = array();
	protected $_aAuthorParams = array();
	protected $_aCptParams = array();
	
	
	
	//
	public function init() {
		
		
		$oNavMgmtAdmin = Geko_Wp_NavigationManagement_PluginAdmin::getInstance();
		$oPageManager = $oNavMgmtAdmin->getPageManager();
		
		//// post types, normalized
		
		$this->setPostTypeParams( array(
			self::TYPE_CAT => array( 'slug' => 'category', 'title' => 'Category' ),
			self::TYPE_AUTH => array( 'slug' => 'author', 'title' => 'Author' ),
			self::TYPE_CPT => array( 'slug' => 'post_type', 'title' => 'Post Type' )			
		) );
		
		
		
		//// categories normalized
		
		$oCatPlugin = $oPageManager->getPlugin( 'Geko_Wp_NavigationManagement_Page_Category' );
		
		$this->setCatParams( $oCatPlugin->getCatParams() );
		
		
		
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
		
		$this->setAuthorParams( $aAuthorsNorm );
		
		
		//// custom post types normalized
		
		$oCptPlugin = $oPageManager->getPlugin( 'Geko_Wp_NavigationManagement_Page_CustomType' );
		
		$this->setCptParams( $oCptPlugin->getCptParams() );
		
	}

	//
	public function setPostTypeParams( $aPostTypeParams ) {
		$this->_aPostTypeParams = $aPostTypeParams;
		return $this;
	}
	
	//
	public function setCatParams( $aCatParams ) {
		$this->_aCatParams = $aCatParams;
		return $this;
	}
	
	//
	public function setAuthorParams( $aAuthorParams ) {
		$this->_aAuthorParams = $aAuthorParams;
		return $this;
	}
	
	//
	public function setCptParams( $aCptParams ) {
		$this->_aCptParams = $aCptParams;
		return $this;
	}
	
	
	
	//
	public function getDefaultParams() {
		
		$aParams = array_merge( parent::getDefaultParams(), array(
			'post_type_id' => key( $this->_aPostTypeParams ),
			'cat_type' => key( $this->_aCatParams[ 'cat_types' ] ),
			'cat_id' => key( $this->_aCatParams[ 'cats_norm' ] ),
			'author_id' => key( $this->_aAuthorParams ),
			'custom_post_type' => key( $this->_aCptParams ),
			'hide' => TRUE
		) );
				
		return $aParams;
	}
	
	
	
	//
	public function getManagementData() {
		
		$aData = array_merge( parent::getManagementData(), array(
			'post_types' => $this->_aPostTypeParams,
			'cat_params' => $this->_aCatParams,
			'author_params' => $this->_aAuthorParams,
			'cpt_params' => $this->_aCptParams
		) );
		
		return $aData;
	}
	
	
	
	
	
	//
	public function outputStyle() {
		?>
		.type-##type## { background-color: lavender; border: dotted 1px indigo; }
		<?php
	}
	
	
	
	//
	public function outputHtml() {
		
		?>
		<label for="##nvpfx_type##post_type_id">Post Attribute to Match</label>
		<select name="##nvpfx_type##post_type_id" id="##nvpfx_type##post_type_id" class="text ui-widget-content ui-corner-all"></select>		
		
		<label for="##nvpfx_type##cat_type">Active on Matching Category Type</label>
		<select name="##nvpfx_type##cat_type" id="##nvpfx_type##cat_type" class="text ui-widget-content ui-corner-all"></select>		
		
		<label for="##nvpfx_type##cat_id">Category Title</label>
		<select name="##nvpfx_type##cat_id" id="##nvpfx_type##cat_id" class="text ui-widget-content ui-corner-all"></select>		
		
		<label for="##nvpfx_type##author_id">Active on Matching Author</label>
		<select name="##nvpfx_type##author_id" id="##nvpfx_type##author_id" class="text ui-widget-content ui-corner-all"></select>
		
		<label for="##nvpfx_type##custom_post_type">Active on Matching Custom Post Type</label>
		<select name="##nvpfx_type##custom_post_type" id="##nvpfx_type##custom_post_type" class="text ui-widget-content ui-corner-all"></select>
		<?php
	}
	
	
	//
    public static function getDescription() {
    	return 'Wordpress Post';
    }
    
}


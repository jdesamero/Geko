<?php
/*
 * "geko_core/library/Geko/Wp/NavigationManagement/Page/Post.php"
 * https://github.com/jdesamero/Geko
 *
 * Copyright (c) 2013 Joel Desamero.
 * Licensed under the MIT license.
 */

//
class Geko_Wp_NavigationManagement_Page_Post
	extends Geko_Navigation_Page_ImplicitLabelAbstract
{
	
	//
	protected $_iPostTypeId;
	protected $_sCatType;
	protected $_iCatId;
	protected $_iAuthorId;
	protected $_sCpt;
	
	protected $oPost;
	
	
	
	
	
	//// object methods
	
	//
	public function setPostTypeId( $iPostTypeId ) {
		$this->_iPostTypeId = $iPostTypeId;
		return $this;
	}
	
	//
	public function getPostTypeId() {
		return $this->_iPostTypeId;
	}
	
	
	
	//
	public function setCatType( $sCatType ) {
		$this->_sCatType = $sCatType;
		return $this;	
	}
	
	
	//
	public function setCatId( $iCatId ) {
		$this->_iCatId = $iCatId;
		return $this;
	}
	
	//
	public function getCatId() {
		return $this->_iCatId;
	}
	
	
	
	
	//
	public function setAuthorId( $iAuthorId ) {
		$this->_iAuthorId = $iAuthorId;
		return $this;
	}
	
	//
	public function getAuthorId() {
		return $this->_iAuthorId;
	}
	
	
	//
	public function setCustomPostType( $sCpt ) {
		$this->_sCpt = $sCpt;
		return $this;
	}
	
	//
	public function getCustomPostType() {
		return $this->_sCpt;
	}

	
	
	
	
	
	//
	public function getPost() {
		
		if ( !$this->oPost ) {
			$this->oPost = new Geko_Wp_Post();
		}
		
		return $this->oPost;
	}
	
	
	//
	public function getHref() {
		return $this->getPost()->getUrl();
	}
	
	//
	public function getImplicitLabel() {
		return $this->getPost()->getTheTitle();
	}
	
	
	//
	public function toArray() {
		return array_merge(
			parent::toArray(),
			array(
				'post_type_id' => $this->_iPostTypeId,
				'cat_type' => $this->_sCatType,
				'cat_id' => $this->_iCatId,
				'author_id' => $this->_iAuthorId,
				'custom_post_type' => $this->_sCpt
			)
		);
	}
	
	//
	public function isCurrentPost() {
		
		if ( is_single() ) {
			
			global $wp_query;
			$oPost = $this->getPost();
			
			if ( Geko_Wp_NavigationManagement_PageManager_Post::TYPE_CAT == $this->_iPostTypeId ) {
				
				$bMatch = FALSE;
				
				$aTaxonomies = get_taxonomies( array(), 'names' );
				
				foreach ( $aTaxonomies as $sTx ) {
					if ( $bMatch = has_term( $this->_iCatId, $sTx ) ) {
						break;
					}
				}
				
				$iMatchId = $this->_iCatId;
			
			} elseif ( Geko_Wp_NavigationManagement_PageManager_Post::TYPE_AUTH == $this->_iPostTypeId ) {
				
				$bMatch = ( $this->_iAuthorId == $wp_query->post->post_author );
				$iMatchId = $this->_iAuthorId;

			} elseif ( Geko_Wp_NavigationManagement_PageManager_Post::TYPE_CPT == $this->_iPostTypeId ) {
				
				$bMatch = is_singular( $this->_sCpt );
				$iMatchId = $this->_sCpt;
				
			} else {
				
				$bMatch = NULL;
				$iMatchId = NULL;
			}
			
			$aArgs = array( sprintf( '%s::single', __METHOD__ ), $bMatch, $this->_iPostTypeId, $iMatchId, $oPost );
			
			return call_user_func_array( 'apply_filters', $aArgs );
			
		}
		
		return FALSE;
	}
	
	//
	public function isActive( $bRecursive = FALSE ) {
		
		if ( $this->_inactive ) {
			$this->_active = FALSE;
		} else {
			$this->_active = $this->isCurrentPost();
		}
		
		return parent::isActive( $bRecursive );
	}
	
	
}



<?php

//
class Geko_Wp_NavigationManagement_Page_Post
	extends Geko_Navigation_Page_ImplicitLabelAbstract
{
	
	//
	protected $_postTypeId;
	protected $_catId;
	protected $_authorId;
	
	protected $oPost;
	
	
	//// object methods
	
	//
	public function setPostTypeId( $postTypeId ) {
		$this->_postTypeId = $postTypeId;
		return $this;
	}
	
	//
	public function getPostTypeId() {
		return $this->_postTypeId;
	}
	
	
	
	//
	public function setCatId( $catId ) {
		$this->_catId = $catId;
		return $this;
	}
	
	//
	public function getCatId() {
		return $this->_catId;
	}
	
	
	//
	public function setAuthorId( $authorId ) {
		$this->_authorId = $authorId;
		return $this;
	}
	
	//
	public function getAuthorId() {
		return $this->_authorId;
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
				'post_type_id' => $this->_postTypeId,
				'cat_id' => $this->_catId,
				'author_id' => $this->_authorId
			)
		);
	}
	
	//
	public function isCurrentPost() {
		
		if ( is_single() ) {
			
			global $wp_query;
			$oPost = $this->getPost();
			
			if ( Geko_Wp_NavigationManagement_PageManager_Post::TYPE_CAT == $this->_postTypeId ) {
				$bMatch = in_category( $this->_catId );
				$iMatchId = $this->_catId;
			} elseif ( Geko_Wp_NavigationManagement_PageManager_Post::TYPE_AUTH == $this->_postTypeId ) {
				$bMatch = ( $this->_authorId == $wp_query->post->post_author );
				$iMatchId = $this->_authorId;
			} else {
				$bMatch = NULL;
				$iMatchId = NULL;
			}
			
			$aArgs = array( sprintf( '%s::single', __METHOD__ ), $bMatch, $this->_postTypeId, $iMatchId, $oPost );
			
			return call_user_func_array( 'apply_filters', $aArgs );
			
		}
		
		return FALSE;
	}
	
	//
	public function isActive( $recursive = FALSE ) {
		
		if ( $this->_inactive ) {
			$this->_active = FALSE;
		} else {
			$this->_active = $this->isCurrentPost();
		}
		
		return parent::isActive( $recursive );
	}
	
	
}



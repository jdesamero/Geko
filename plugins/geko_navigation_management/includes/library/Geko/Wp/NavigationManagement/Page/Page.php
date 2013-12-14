<?php

//
class Geko_Wp_NavigationManagement_Page_Page
	extends Geko_Navigation_Page_ImplicitLabelAbstract
{
	protected $_pageId;
	
	
	//// object methods
	
	//
	public function setPageId( $pageId ) {
		$this->_pageId = $pageId;
		return $this;
	}
	
	//
	public function getPageId() {
		return $this->_pageId;
	}
	
	//
	public function getHref() {
		return apply_filters(
			__METHOD__ . '::page',
			get_permalink( $this->_pageId ),
			$this->_pageId,
			$this
		);
	}
	
	//
	public function getImplicitLabel() {
		return apply_filters(
			__METHOD__ . '::page',
			get_the_title( $this->_pageId ),
			$this->_pageId,
			$this
		);
	}
	
	
	//
	public function toArray() {
		return array_merge(
			parent::toArray(),
			array( 'page_id' => $this->_pageId )
		);
	}
	
	//
	public function isCurrentPage() {
		return apply_filters(
			__METHOD__ . '::page',
			is_page( $this->_pageId ),
			$this->_pageId,
			$this
		);
	}
	
	//
	public function isActive( $recursive = FALSE ) {
		
		if ( $this->_inactive ) {
			$this->_active = FALSE;
		} else {
			$this->_active = $this->isCurrentPage();
		}
		
		return parent::isActive( $recursive );
	}
	
	
}



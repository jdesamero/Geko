<?php
/*
 * "geko_core/library/Geko/Wp/NavigationManagement/Page/Page.php"
 * https://github.com/jdesamero/Geko
 *
 * Copyright (c) 2013 Joel Desamero.
 * Licensed under the MIT license.
 */

//
class Geko_Wp_NavigationManagement_Page_Page
	extends Geko_Navigation_Page_ImplicitLabelAbstract
{
	protected $_iPageId;
	
	
	//// object methods
	
	//
	public function setPageId( $iPageId ) {
		$this->_iPageId = $iPageId;
		return $this;
	}
	
	//
	public function getPageId() {
		return $this->_iPageId;
	}
	
	//
	public function getHref() {
		return apply_filters(
			sprintf( '%s::page', __METHOD__ ),
			get_permalink( $this->_iPageId ),
			$this->_iPageId,
			$this
		);
	}
	
	//
	public function getImplicitLabel() {
		return apply_filters(
			sprintf( '%s::page', __METHOD__ ),
			get_the_title( $this->_iPageId ),
			$this->_iPageId,
			$this
		);
	}
	
	
	//
	public function toArray() {
		return array_merge(
			parent::toArray(),
			array( 'page_id' => $this->_iPageId )
		);
	}
	
	//
	public function isCurrentPage() {
		
		$iPageId = $this->_iPageId;
		
		return apply_filters(
			sprintf( '%s::page', __METHOD__ ),
			Geko_String::coalesce( is_page( $iPageId ), is_single( $iPageId ) ),
			$iPageId,
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



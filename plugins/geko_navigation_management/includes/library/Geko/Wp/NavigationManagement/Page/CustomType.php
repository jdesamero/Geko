<?php
/*
 * "geko_navigation_management/includes/library/Geko/Wp/NavigationManagement/Page/CustomType.php"
 * https://github.com/jdesamero/Geko
 *
 * Copyright (c) 2013 Joel Desamero.
 * Licensed under the MIT license.
 */

class Geko_Wp_NavigationManagement_Page_CustomType
	extends Geko_Navigation_Page_ImplicitLabelAbstract
{
	
	//
	protected $_sCpt;
	protected $_oCpt;
	
	
	//// object methods
	
	//
	public function setCustomPostType( $sCpt ) {
		
		$this->_sCpt = $sCpt;
		$this->_oCpt = get_post_type_object( $sCpt );
		
		return $this;
	}
	
	//
	public function getCustomPostType() {
		return $this->_sCpt;
	}
	
	
	
	
	
	
	//
	public function getHref() {

		$sCpt = $this->_sCpt;
		
		return ( $sCpt ) ? get_post_type_archive_link( $sCpt ) : '' ;
	}
	
	//
	public function getImplicitLabel() {
		
		$oCpt = $this->_oCpt;
		
		return ( $oCpt ) ? $oCpt->labels->menu_name : '' ;
	}
	
	
	
	//
	public function toArray() {
		
		return array_merge(
			parent::toArray(),
			array( 'custom_post_type' => $this->_sCpt )
		);
	}
	
	//
	public function isCurrentCustomType() {
		
		$sCpt = $this->_sCpt;
		
		return apply_filters( __METHOD__, is_post_type_archive( $sCpt ), $sCpt );
	}
	
	
	//
	public function isActive( $bRecursive = FALSE ) {
		
		if ( $this->_inactive ) {
			$this->_active = FALSE;
		} else {
			$this->_active = $this->isCurrentCustomType();
		}
		
		return parent::isActive( $bRecursive );
	}
	
	
}


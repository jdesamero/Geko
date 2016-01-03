<?php
/*
 * "geko_core/library/Geko/Wp/NavigationManagement/Page/RoleItem.php"
 * https://github.com/jdesamero/Geko
 *
 * Copyright (c) 2013 Joel Desamero.
 * Licensed under the MIT license.
 */

//
class Geko_Wp_NavigationManagement_Page_RoleItem
	extends Geko_Navigation_Page_ImplicitLabelAbstract
{
	
	protected $_roleId;
	
	protected $oRole;
	
	
	//// object methods
	
	
	//
	public function setRoleId( $roleId ) {
		$this->_roleId = $roleId;
		return $this;
	}
	
	//
	public function getRoleId() {
		return $this->_roleId;
	}
	
	//
	public function getRole() {
		if ( !$this->oRole ) {
			$this->oRole = new Geko_Wp_Role( $this->_roleId );
		}
		
		return $this->oRole;
	}
	
	
	//
	public function getHref() {
		return $this->getRole()->getUrl();
	}
	
	//
	public function getImplicitLabel() {
		
		$oRole = $this->getRole();
		
		if (
			( $oRole->isValid() ) && 
			( $oRewrite = $oRole->getRewrite() ) && 
			( $oRewrite->isSingle() )
		) {
			
			if ( $sEntityClass = $oRole->getEntityClass() ) {
				$oItem = new $sEntityClass();	// default role item
				$sImplicitLabel = apply_filters(
					__METHOD__,
					$oItem->getTheTitle(),
					$oItem
				);
				return $sImplicitLabel;
			}
			
		}
		
		return '';
	}
	
	
	//
	public function toArray() {
		return array_merge(
			parent::toArray(),
			array(
				'role_id' => $this->_roleId
			)
		);
	}
	
	
	
	//
	public function isCurrentRoleItem() {
		
		$oRole = $this->getRole();
		
		if (
			( $oRole->isValid() ) && 
			( $oRewrite = $oRole->getRewrite() ) && 
			( $oRewrite->isSingle() )
		) {
			
			if ( $sEntityClass = $oRole->getEntityClass() ) {
				$oItem = new $sEntityClass();	// default role item
				$bCurrentRoleItem = apply_filters(
					__METHOD__ . '::role',
					( $oItem->getRoleId() == $this->_roleId ),
					$oRewrite,
					$oItem,
					$this
				);				
				return $bCurrentRoleItem;
			}
			
		}
		
		return FALSE;
	}
	
	
	//
	public function isActive( $recursive = FALSE ) {
		
		if ( $this->_inactive ) {
			$this->_active = FALSE;
		} else {
			$this->_active = $this->isCurrentRoleItem();
		}
		
		return parent::isActive( $recursive );
	}
	
	
}


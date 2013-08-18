<?php

class Geko_Wp_NavigationManagement_Page_Role
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
		$sHref = apply_filters(
			__METHOD__,
			$this->getRole()->getUrl(),
			$this
		);
		return $sHref;
	}
	
	//
	public function getImplicitLabel() {
		return $this->getRole()->getTheTitle();
	}
	
	
	//
	public function toArray() {
		return array_merge(
			parent::toArray(),
			array(
				'role_id' => $this->_roleId
			));
	}
	
	
	
	//
	public function isCurrentRole() {
		
		$oRole = $this->getRole();

		if (
			( $oRole->isValid() ) && 
			( $oRewrite = $oRole->getRewrite() )
		) {			
				
			if ( $oRewrite->isSingle() ) {
				// is single role item
				if ( $oRole->isAll() ) {
					return TRUE;
				} else {
					if ( $sEntityClass = $oRole->getEntityClass() ) {
						$oItem = new $sEntityClass();	// default role item
						$bCurrentRoleSingle = apply_filters(
							__METHOD__ . '::single',
							( $oItem->getRoleId() == $this->_roleId ),
							$oRewrite,
							$oItem,
							$this
						);
						return $bCurrentRoleSingle;
					}
				}
			} else {
				// is list
				$bCurrentRoleList = apply_filters(
					__METHOD__ . '::list',
					$oRewrite->isList( $oRole->getSlug( TRUE ) ),
					$oRewrite,
					$this
				);
				return $bCurrentRoleList;
			}
			
		}
		
		return FALSE;
	}
	
	
	//
	public function isActive( $recursive = FALSE ) {
		
		if ( $this->_inactive ) {
			$this->_active = FALSE;
		} else {
			$this->_active = $this->isCurrentRole();
		}
		
		return parent::isActive( $recursive );
	}
	
	
}


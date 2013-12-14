<?php

// listing
abstract class Geko_Wp_Role_Type_Abstract extends Geko_Singleton_Abstract
{
	protected $_sTypeName = '';
	protected $_sTypeCode = '';
	
	protected $_sRoleEntityClass = '';
	protected $_sRewriteClass = '';
	protected $_sManageClass = '';
	
	
	protected $_bCalledInit = FALSE;
	protected $_bCalledInitTheme = FALSE;
	protected $_bCalledInitAdmin = FALSE;
	
	
	//
	protected $_aCounts;
	protected $_bPopulatedCounts = FALSE;
	
	protected $_oCurrentRoleInstance;
	
	
	//// construction
	
	//
	protected function __construct() {
		
		$sBaseClass = str_replace( '_RoleType', '', get_class( $this ) );
		
		if ( !$this->_sRoleEntityClass ) {
			$sClass = $sBaseClass;
			$this->_sRoleEntityClass = @class_exists( $sClass ) ? $sClass : '';
		}
		
		if ( !$this->_sRewriteClass ) {
			$sClass = $sBaseClass . '_Rewrite';
			$this->_sRewriteClass = @class_exists( $sClass ) ? $sClass : '';
		}
		
		if ( !$this->_sManageClass ) {
			$sClass = $sBaseClass . '_Manage';
			$this->_sManageClass = @class_exists( $sClass ) ? $sClass : '';
		}
	}
	
	// calling init() ensures add() is only called once while in admin mode
	public function init() {
		
		if ( !$this->_bCalledInit ) {
			$this->add();
			$this->_bCalledInit = TRUE;
		}
		
		if ( !$this->_bCalledInitTheme && !is_admin() ) {
			$this->addTheme();
			$this->_bCalledInitTheme = TRUE;
		}
		
		if ( !$this->_bCalledInitAdmin && is_admin() ) {
			$this->addAdmin();
			$this->_bCalledInit = TRUE;
		}
		
		return $this;
	}
	
	//
	public function reconcileAssigned() {
		return $this;
	}
	
	//
	public function reconcileRoleOnUpdate( Geko_Wp_Role $oOldRole, Geko_Wp_Role $oNewRole ) {
		return $this;
	}
	
	//
	public function getRoleAssignedCount( $iRoleId ) {
		
		if ( !$this->_bPopulatedCounts ) {
			$this->populateCounts();
			$this->_bPopulatedCounts = TRUE;
		}
		
		if ( isset( $this->_aCounts[ $iRoleId ] ) ) {
			return $this->_aCounts[ $iRoleId ];
		} else {
			return 0;
		}
	}
	
	// generate the link that goes to the listing of corresponing items,
	// may it be users or other entities assigned a role
	public function getRoleAssignedCountUrl( Geko_Wp_Role $oRole ) {
		if ( $this->_sManageClass ) {
			return sprintf( '%s?page=%s&role_id=%d', Geko_Uri::getUrl( 'wp_admin' ), $this->_sManageClass, $oRole->getId() );
		} else {
			return '';
		}
	}
	
	//
	public function getRoleCapabilities( Geko_Wp_Role $oRole ) {
		return FALSE;
	}
	
	//
	public function getRoleLevel( Geko_Wp_Role $oRole ) {
		return FALSE;
	}
	
	// the permalink lists the items belonging to a particular role
	public function getRolePermalink( Geko_Wp_Role $oRole ) {
		if ( $this->_sRewriteClass ) {
			return sprintf(
				Geko_Singleton_Abstract::getInstance(
					$this->_sRewriteClass
				)->getListPermastruct(),
				$oRole->getSlug()
			);			
		} else {
			return '';
		}
	}
	
	
	
	
	
	//// some methods delegated by a Geko_Wp_Role object
	
	//
	public function getRoleDefaultEntityValue() {
		
		if ( $this->_sRewriteClass ) {
			return Geko_Singleton_Abstract::getInstance(
				$this->_sRewriteClass
			)->getListVar();
		} else {
			return NULL;
		}
	}
	
	//
	public function getRoleRewrite() {
		
		if ( $this->_sRewriteClass ) {
			return Geko_Singleton_Abstract::getInstance(
				$this->_sRewriteClass
			);
		} else {
			return NULL;
		}
	}
	
	//
	public function getRoleEntityClass() {
		return $this->_sRoleEntityClass;
	}
	
	
	
	
	//
	public function getName() {
		return $this->_sTypeName;
	}
	
	public function getCode() {
		return ( $this->_sTypeCode ) ?
			$this->_sTypeCode :
			sanitize_title( $this->getName() )
		;
	}
	
	
	
	
	//// role instance accessors
	
	//
	public function getCurrentRoleInstance() {
		
		if ( !$this->_oCurrentRoleInstance ) {
			$this->_oCurrentRoleInstance = Geko_Wp_Role_Manage::getInstance()
				->init()
				->initEntities()
				->getCurrentEntity()
			;
		}
		
		return $this->_oCurrentRoleInstance;
	}
	
	//
	public function getCurrentRoleType() {
		
		if ( $oRole = $this->getCurrentRoleInstance() ) {
			return $oRole->getType();
		}
		
		return FALSE;
	}
	
	//
	public function isSameAsCurrentRoleType() {
		return ( $this->getCurrentRoleType() == $this->getName() );
	}
	
	
	abstract public function populateCounts();
	
	// to be implemented by subclasses, typically consists of calls to add_action()
	protected function add() { }
	protected function addTheme() { }
	protected function addAdmin() { }
	
	
}



<?php

//
class Geko_Wp_Role extends Geko_Wp_Entity
{	

	protected $_sEntityIdVarName = 'role_id';
	protected $_sEntitySlugVarName = 'role_slug';
	
	
	//// object oriented functions
	
	protected $_oRoleType;
	
	//
	public function init() {
		
		parent::init();
		
		$this
			->setEntityMapping( 'id', 'role_id' )
			->setEntityMapping( 'content', 'description' )
			->setEntityMapping( 'excerpt', 'description' )
		;
		
		return $this;
	}
	
	//
	public function constructEnd() {
		
		$this->_oRoleType = Geko_Wp_Role_Types::getInstance()
			->getRoleTypeObject( $this->getTypeCode() )
		;
		
		return parent::constructEnd();	
	}
	
	
	//
	public function formatEntity( $mEntity ) {
		
		// check for the all pseudo type
		$aRegs = array();
		
		if (
			is_string( $mEntity ) && 
			preg_match( '/^all-([0-9a-zA-Z_-]+)$/', $mEntity, $aRegs )
		) {
			$mEntity = self::createPseudoAllEntity( $aRegs[1] );
		}
		
		return parent::formatEntity( $mEntity );
	}
	
	
	
	//
	public function getType() {
		return $this->getEntityPropertyValue('type');
	}
	
	//
	public function getTypeCode() {
		return sanitize_title( $this->getType() );
	}
	
	
	
	//
	public function getTheTitle() {
		return Geko_Inflector::pluralize( $this->getTitle() );
	}
	
	
	
	////
	public function isAll() {
		return $this->getEntityPropertyValue('is_all');
	}
	
	
	//// relating to the role type object
	
	//
	public function hasRoleTypeObject() {
		return ( $this->_oRoleType ) ? TRUE : FALSE;
	}

	//
	public function getRoleTypeObject() {
		return $this->_oRoleType;
	}
	
	
	//// methods that use the role type object
	
	//
	public function getAssignedCount() {
		
		if ( $this->_oRoleType ) {
			return $this->_oRoleType->getRoleAssignedCount( $this->getId() );
		}
		
		return 0;
	}

	//
	public function getAssignedCountUrl() {
		
		if ( $this->_oRoleType ) {
			return $this->_oRoleType->getRoleAssignedCountUrl( $this );
		}
		
		return 0;
	}
	
	//
	public function getAssignedCountLink() {
		
		$iCount = $this->getAssignedCount();
		
		if ( $iCount ) {
			return sprintf( '<a href="%s">%d</a>', $this->getAssignedCountUrl(), $iCount );
		} else {
			return 0;
		}
	}
	
	// get capabilities associated with the role
	// defer to the $this->_oRoleType object if it's there
	public function getCapabilities() {
		
		if ( $this->_oRoleType ) {
			return $this->_oRoleType->getRoleCapabilities( $this );
		}
		
		return FALSE;
	}
	
	// get level associated with the role
	// defer to the $this->_oRoleType object if it's there
	public function getLevel() {
		
		if ( $this->_oRoleType ) {
			return $this->_oRoleType->getRoleLevel( $this );
		}
		
		return FALSE;
	}
	
	//
	public function getPermalink() {
		
		$sPermalink = $this->_oRoleType->getRolePermalink( $this );
		
		if ( $this->isAll() ) {
			$sPermalink = str_replace( sprintf( 'all-%s', $this->getTypeCode() ), 'all', $sPermalink );
		}
		
		return $sPermalink;
	}
	
	// if $bFormat is set to true, then manipulate the slug output as required
	public function getSlug( $bFormat = FALSE ) {
		
		$sSlug = parent::getSlug();
		if ( $this->isAll() && $bFormat ) $sSlug = 'all';
		
		return $sSlug;	
	}
	
	
	// need to establish the role type
	// potentially on the best matching rule
	public function getDefaultEntityValue() {
		return Geko_Wp_Role_Types::getInstance()->getCurrentType();
	}
	
	//
	public function getRewrite() {
		return $this->_oRoleType->getRoleRewrite( $this );
	}
	
	//
	public function getEntityClass() {
		return $this->_oRoleType->getRoleEntityClass( $this );
	}
	
	
	
	////

	public static function createPseudoAllEntity( $sRoleType ) {
		
		$oEntity = new StdClass;
		
		$sItem = strtolower( str_replace( array( '-', '_') , ' ', $sRoleType ) );
		$sItem = ucwords( trim( str_replace( 'role', '', $sItem ) ) );
		
		$sItemPlural = Geko_Inflector::pluralize( $sItem );
		
		$oEntity->role_id = Geko_Wp_Role_Types::getInstance()->getPseudoRoleTypeId(
			sanitize_title( $sRoleType )
		);
		
		$oEntity->title = sprintf( 'All %s', $sItem );
		$oEntity->slug = $oEntity->role_id;		// same as the pseudo role id
		$oEntity->type = $sRoleType;
		$oEntity->description = sprintf( 'Represents all %s', strtolower( $sItemPlural ) );
		$oEntity->is_all = TRUE;
		
		return $oEntity;
	}
	
	// create a pseudo-role object meant to represent items
	// beloning to a type
	public static function createPseudoAll( $sRoleType ) {
		
		$sClass = get_called_class();
		
		return new $sClass(
			call_user_func( array( $sClass, 'createPseudoAllEntity' ), $sRoleType )
		);
	}
	
	
	
}




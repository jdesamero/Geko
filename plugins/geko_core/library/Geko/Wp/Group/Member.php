<?php

//
class Geko_Wp_Group_Member extends Geko_Wp_Entity
{
	protected $_sUserEntityClass = 'Geko_Wp_User';
	protected $_sGroupEntityClass = 'Geko_Wp_Group';
	protected $_sGroupManageClass = '';
	
	
	
	//
	public function __construct( $mEntity = NULL, $oQuery = NULL, $aData = array(), $aQueryParams = NULL ) {
		
		parent::__construct( $mEntity, $oQuery, $aData, $aQueryParams );
		
		//
		$this->_sGroupManageClass = Geko_Class::resolveRelatedClass(
			$this->_sGroupEntityClass, '', '_Manage', $this->_sGroupManageClass
		);
		
	}
	
	
	//
	public function getUserFullName() {
		return Geko_String::coalesce(
			trim( $this->getFirstName() . ' ' . $this->getLastName() ),
			$this->getUserDisplayName()
		);
	}
	
	//
	public function getDateTimeRequested( $sFormat = '' ) {
		return $this->mysql2Date( $this->getDateRequested(), $sFormat );
	}

	//
	public function getDateTimeJoined( $sFormat = '' ) {
		return $this->mysql2Date( $this->getDateJoined(), $sFormat );
	}
	
	//
	public function getUserEditUrl() {
		return sprintf( '%s?user_id=%d', Geko_Uri::getUrl( 'wp_user_edit' ), $this->getUserId() );
	}
	
	//
	public function getGroupEditUrl() {
		return sprintf( '%s?page=%s&group_id=%d', Geko_Uri::getUrl( 'wp_admin' ), $this->_sGroupManageClass, $this->getGroupId() );
	}
	
	//
	public function getStatus() {
		return ucfirst( Geko_Wp_Options_MetaKey::getKey( $this->getStatusId() ) );
	}
	
}



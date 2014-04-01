<?php

//
class Geko_Wp_User_Location_Manage extends Geko_Wp_Location_Manage
{
	
	protected $_sObjectType = 'user';
	protected $_sUserType = 'user';			// ???
	protected $_sSubAction = 'user';
	protected $_sSectionLabel = 'Address';
	
	
	
	
	// return a prefix
	public function getPrefix() {
		return $this->_sUserType;
	}
	
	// !!!
	public function addAdmin() {
		
		parent::addAdmin();
		
		add_action( sprintf( 'admin_init_%s', $this->_sSubAction ), array( $this, 'install' )  );		
		add_action( sprintf( 'admin_head_%s', $this->_sSubAction ), array( $this, 'addAdminHead' )  );
		
		////
		
		add_action( 'show_user_profile', array( $this, 'outputForm' ), 9 );
		add_action( 'edit_user_profile', array( $this, 'outputForm' ), 9 );
		add_action( 'personal_options_update', array( $this, 'updateType' ) );
		add_action( 'edit_user_profile_update', array( $this, 'updateType' ) );
		add_action( 'deleted_user', array( $this, 'deleteType' ) );
		
		return $this;
	}
	
	//
	public function resolveUserId( $iUserId = NULL ) {
		if ( NULL === $iUserId ) {
			global $user_id;
			return Geko_String::coalesce( $user_id, $_GET[ 'user_id' ] );
		}
		return $iUserId;
	}
	
	//
	public function initEntities( $oMainEnt = NULL, $aParams = array() ) {
		$this->_iObjectId = $this->resolveUserId();
		return parent::initEntities( $oMainEnt, $aParams );
	}
	
	//
	public function outputForm() {
		$this->initEntities();
		parent::outputForm();
	}
	
	
	
	//// sub crud methods
	
	// no insert for users
	// public function insertType() { }
	
	//
	public function updateType( $iUserId = NULL ) {
		$this->_iObjectId = $this->resolveUserId( $iUserId );
		$this->save( array(), 'update' );
	}
	
	//
	public function deleteType( $iUserId = NULL ) {

		$this->_iObjectId = $this->resolveUserId( $iUserId );
		$this->delete();
		
	}
	
	
	
}



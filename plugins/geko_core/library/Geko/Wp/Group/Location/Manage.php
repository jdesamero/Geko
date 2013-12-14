<?php

//
class Geko_Wp_Group_Location_Manage extends Geko_Wp_Location_Manage
{
	
	protected $_sObjectType = 'group';
	protected $_sGroupType = 'group';			// ???
	protected $_sSectionLabel = 'Address';
	protected $_bSubExtraFields = TRUE;
	
	
	
	// return a prefix
	public function getPrefix() {
		return $this->_sGroupType;
	}
	
	
	//
	public function initEntities( $oMainEnt = NULL, $aParams = array() ) {
		// HACKISH!!!
		$this->_iObjectId = intval( Geko_String::coalesce( $_GET[ 'child_id' ], $_GET[ 'group_id' ] ) );
		return parent::initEntities( $oMainEnt, $aParams );
	}
		
	
	
}


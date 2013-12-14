<?php

//
class Geko_Wp_Contact_Plugin extends Geko_Wp_Options_Plugin
{
	
	protected $_sObjectType = '';
	protected $_sContactSubType = '';
	
	protected $_aFields = array();
	protected $_aFieldLabels = array();
	protected $_aFieldDescriptions = array();
	
	
	//
	public function __construct( $sType = '' ) {
		
		parent::__construct();
		
		if ( $this->_oPlgManage ) {
		
			$this->_aFields = $this->_oPlgManage->getFields();
			$this->_aFieldDescriptions = $this->_oPlgManage->getFieldDescriptions();
			$this->_aFieldLabels = $this->_oPlgManage->getFieldLabels();
			
			$this->setType( $sType );
			
		}
		
	}
	
	//// accessors
		
	// shorthand
	public function setType( $sType ) {
		
		if ( $sType ) {
			
			// format: <object type>|<contact sub-type>|<prefix>
			// if no <contact sub-type>, then use <object type>
			// if no <prefix>, then use <contact sub-type>
			
			$aType = explode( '|', $sType );
			
			$this->_sObjectType = trim( $aType[ 0 ] );

			$this->_sContactSubType = trim( $aType[ 1 ] );
			if ( !$this->_sContactSubType ) $this->_sContactSubType = $this->_sObjectType;
			
			$this->_sPrefix = trim( $aType[ 2 ] );
			if ( !$this->_sPrefix ) $this->_sPrefix = $this->_sContactSubType;
			
		}
		
		return $this;
	}
	
	//
	public function setObjectType( $sObjectType ) {
		$this->_sObjectType = $sObjectType;
		return $this;
	}
	
	//
	public function getObjectType() {
		return $this->_sObjectType;
	}
	
	//
	public function setContactSubType( $sContactSubType ) {
		$this->_sContactSubType = $sContactSubType;
		return $this;
	}
	
	//
	public function getContactSubType() {
		return $this->_sContactSubType;
	}
	
	
	
	//
	public function initEntities( $oMainEnt, $aParams = array() ) {
		
		parent::initEntities( $oMainEnt, $aParams );
		
		if ( !$this->_oCurrentEntity && $this->_iObjectId ) {
			
			$aParams[ 'object_id' ] = $this->_iObjectId;
			
			if ( $this->_sObjectType ) {
				$aParams[ 'object_type' ] = $this->_sObjectType;
			}
			
			if ( $this->_sContactSubType ) {
				$aParams[ 'subtype_id' ] = Geko_Wp_Options_MetaKey::getId( $this->_sContactSubType );
			}
			
			$this->_oCurrentEntity = call_user_func(
				array( $this->getCurrentEntityClass(), 'getOne' ), $aParams, FALSE
			);
			
			if ( $this->_oCurrentEntity->isValid() ) {
				$this->_iCurrentEntityId = $this->_oCurrentEntity->getId();
			}
			
		}
		
		return $this;
	}
		
	
	
	// format <field>|<label>|<description>
	public function setFields( $aFieldVals ) {
		
		foreach ( $aFieldVals as $sFieldVal ) {
			
			$aFieldVal = explode( '|', $sFieldVal );
			$sField = trim( $aFieldVal[ 0 ] );
			$aFields[] = $sField;
			
			if ( $sLabel = trim( $aFieldVal[ 1 ] ) ) {
				$this->_aFieldLabels[ $sField ] = $sLabel;
			}
			
			if ( $sDescription = trim( $aFieldVal[ 2 ] ) ) {
				$this->_aFieldDescriptions[ $sField ] = $sDescription;
			}
		}
		
		$this->_aFields = $aFields;
		return $this;
	}
	
	//
	public function getFields() {
		return $this->_aFields;
	}
	
	//
	public function setFieldLabels( $aFieldLabels, $bOverride = TRUE ) {
		if ( $bOverride ) {
			$this->_aFieldLabels = array_merge( $this->_aFieldLabels, $aFieldLabels );
		} else {
			$this->_aFieldLabels = $aFieldLabels;
		}
		return $this;
	}
	
	//
	public function getFieldLabels() {
		return $this->_aFieldLabels;
	}
	
	//
	public function setFieldDescriptions( $aFieldDescriptions, $bOverride = TRUE ) {
		if ( $bOverride ) {
			$this->_aFieldDescriptions = array_merge( $this->_aFieldDescriptions, $aFieldDescriptions );
		} else {
			$this->_aFieldDescriptions = $aFieldDescriptions;
		}
		return $this;
	}
	
	//
	public function getFieldDescriptions() {
		return $this->_aFieldDescriptions;
	}
	
	
	
	
	//// rail functionality
		
	// for rail functionality compatibility
	public function setProperties( $aProperties ) {
		
		$sType = '';
		
		if ( $oManage = $this->_oManage ) $sType = $oManage->getType();
		
		if ( $sField = $aProperties[ '__field' ] ) {
			if ( $sType ) $sType .= '|';
			$sType .= $sField;
		}
		
		if ( $sType ) $this->setType( $sType );
		
		if ( $aFields = $aProperties[ 'fields' ] ) {
			$this->setFields( $aFields );
		}
		
		return $this;
	}

}



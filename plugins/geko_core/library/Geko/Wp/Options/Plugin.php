<?php

//
class Geko_Wp_Options_Plugin
{
	
	//
	protected $_sManageClass;
	protected $_oManage;						// the parent management class
	
	protected $_sPluginManageClass;
	protected $_oPlgManage;						// the plugin management class
	
	protected $_iObjectId = 0;
	protected $_iCurrentEntityId;
	protected $_oCurrentEntity;
	protected $_sCurrentEntityClass;
	
	protected $_sPrefix = '';
	protected $_sFormFieldSection = 'main';			// "main" or "pre"
	
	protected $_bSubMainFields = FALSE;
	protected $_bSubExtraFields = FALSE;
	protected $_bUpdateRelatedEntities = FALSE;
	
	protected $_sSectionLabel;
	
	
	//
	public function __construct() {
		
		$this->_sPluginManageClass = Geko_Class::resolveRelatedClass(
			$this, '_Plugin', '_Manage', $this->_sPluginManageClass
		);
		
		if ( $this->_sPluginManageClass ) {
			$this->_oPlgManage = Geko_Singleton_Abstract::getInstance(
				$this->_sPluginManageClass
			);
		}
		
	}
	
	
	
	//// accessors
		
	//
	public function setManage( $oManage ) {
		
		$this->_oManage = $oManage;
		$this->_sManageClass = get_class( $oManage );
		
		return $this;
	}
	
	//
	public function getManage() {
		return $this->_oManage;
	}
	
	// object id is the main entity id, provided by the main manage class
	public function getObjectId() {
		return $this->_oManage->getCurrentEntityId();
	}
	
	//
	public function getPluginManage() {
		return $this->_oPlgManage;
	}
	
	
	
	
	
	// to be implemented by sub-class
	public function initEntities( $oMainEnt, $aParams = array() ) {
		$this->_iObjectId = $this->getObjectId();
		return $this;
	}
	
	//
	public function getCurrentEntity() {
		return $this->_oCurrentEntity;
	}
	
	//
	public function setCurrentEntityClass( $sCurrentEntityClass ) {
		$this->_sCurrentEntityClass = $sCurrentEntityClass;
		return $this;
	}
	
	//
	public function getCurrentEntityClass() {
		return ( $this->_sCurrentEntityClass ) ?
			$this->_sCurrentEntityClass : 
			$this->_oPlgManage->getEntityClass()
		;
	}
	
	
	
	
	
	//
	public function setPrefix( $sPrefix ) {
		$this->_sPrefix = $sPrefix;
		return $this;
	}
	
	//
	public function getPrefix() {
		return $this->_sPrefix;
	}

	//
	public function setFormFieldSection( $sFormFieldSection ) {
		$this->_sFormFieldSection = $sFormFieldSection;
		return $this;
	}
	
	//
	public function getFormFieldSection() {
		return $this->_sFormFieldSection;
	}
	
	//
	public function setSubMainFields( $bSubMainFields ) {
		$this->_bSubMainFields = $bSubMainFields;
		return $this;
	}
	
	//
	public function setSubExtraFields( $bSubExtraFields ) {
		$this->_bSubExtraFields = $bSubExtraFields;
		return $this;
	}
	
	//
	public function setUpdateRelatedEntities( $bUpdateRelatedEntities ) {
		$this->_bUpdateRelatedEntities = $bUpdateRelatedEntities;
		return $this;
	}
	
	//
	public function getUpdateRelatedEntities() {
		return $this->_bUpdateRelatedEntities;
	}
	
	//
	public function setSectionLabel( $sSectionLabel ) {
		$this->_sSectionLabel = $sSectionLabel;
		return $this;
	}
	
	//
	public function getSectionLabel() {
		return $this->_sSectionLabel;
	}
	
	
	//
	public function init() {
		
		if ( $oManage = $this->_oManage ) {

			$sSubAction = $oManage->getActionPrefix();
			
			add_action( 'admin_head_admin::' . $this->_sManageClass, array( $this, 'addAdminHead' ) );
			
			add_filter( $sSubAction . '_init_entities', array( $this, 'initEntities' ) );
			add_filter( $sSubAction . '_getstoredopts', array( $this, 'getStoredSubOptions' ), 10, 2 );

			add_action( $sSubAction . '_add', array( $this, 'doSubAddAction' ), 10, 2 );
			add_action( $sSubAction . '_edit', array( $this, 'doSubEditAction' ), 10, 4 );
			add_action( $sSubAction . '_delete', array( $this, 'doSubDelAction' ), 10, 2 );
			
			if ( $this->_bSubMainFields ) {
				
				add_action( $sSubAction . '_main_fields', array( $this, 'formFields' ), 10, 2 );
				add_action( $sSubAction . '_sub_main_field_titles', array( $this, 'subMainFieldTitles' ), 10, 2 );			// ???
				add_action( $sSubAction . '_sub_main_field_columns', array( $this, 'subMainFieldColumns' ), 10, 2 );		// ???
				add_filter( $sSubAction . '_getstoredsubopts', array( $this, 'getStoredSubOptions' ), 10, 2 );

				add_action( $sSubAction . '_subadd', array( $this, 'doSubAddAction' ), 10, 2 );
				add_action( $sSubAction . '_subedit', array( $this, 'doSubEditAction' ), 10, 4 );
				add_action( $sSubAction . '_subdelete', array( $this, 'doSubDelAction' ), 10, 2 );
				
			}
			
			if ( $this->_bSubExtraFields ) {
				add_action( $sSubAction . '_extra_fields', array( $this, 'outputForm' ), 10, 2 );
			}
			
		}
		
	}
	
	
	
	//// implement hook methods
	
	//
	public function getStoredSubOptions( $aRet, $oMainEnt ) {
		if ( $oPlgManage = $this->_oPlgManage ) {
			return $oPlgManage->getStoredSubOptions( $aRet, $oMainEnt, $this );
		}
		return $aRet;
	}
	
	//
	public function addAdminHead() {
		if ( $oPlgManage = $this->_oPlgManage ) {
			$oPlgManage->addAdminHead( $this );
		}
	}
	
	//
	public function formFields( $oEntity, $sSection ) {
		if (
			( $oPlgManage = $this->_oPlgManage ) && 
			( $sSection == $this->_sFormFieldSection )
		) {
			echo $oPlgManage->setupFields( $this );
		}
	}
	
	//
	public function subMainFieldTitles() {
		if ( $oPlgManage = $this->_oPlgManage ) {
			$oPlgManage->subMainFieldTitles( $this );
		}
	}
	
	//
	public function subMainFieldColumns() {
		if ( $oPlgManage = $this->_oPlgManage ) {
			$oPlgManage->subMainFieldColumns( $this );		
		}
	}
	
	//
	public function outputForm() {
		if ( $oPlgManage = $this->_oPlgManage ) {
			$oPlgManage->outputForm( $this );
		}
	}
	
	//
	public function doSubAddAction( $oMainEnt, $aParams ) {
		if ( $oPlgManage = $this->_oPlgManage ) {
			$oPlgManage->doSubAddAction( $oMainEnt, $aParams, $this );
		}
	}
	
	//
	public function doSubEditAction( $oMainEnt, $oUpdMainEnt, $aParams ) {
		if ( $oPlgManage = $this->_oPlgManage ) {
			$oPlgManage->doSubEditAction( $oMainEnt, $oUpdMainEnt, $aParams, $this );
		}
	}
	
	//
	public function doSubDelAction( $oMainEnt, $aParams ) {
		if ( $oPlgManage = $this->_oPlgManage ) {
			$oPlgManage->doSubDelAction( $oMainEnt, $aParams, $this );
		}
	}
	
	
	
	
	//// rail functionality
	
	//
	public function layoutEnqueue() {
		if ( $oPlgManage = $this->_oPlgManage ) {
			return $oPlgManage->layoutEnqueue( $this );
		}
	}
	
	//
	public function layoutHeadLate() {
		if ( $oPlgManage = $this->_oPlgManage ) {
			return $oPlgManage->layoutHeadLate( $this );
		}
	}
	
	// shorthand for setting plugin properties
	public function setProperties( $aProperties ) {
		return $this;
	}
	
	//
	public function getDetailFields() {
		if ( $oPlgManage = $this->_oPlgManage ) {
			return $oPlgManage->getDetailFields( $this );
		}	
	}
	
	// manipulate primary rail table
	public function addDetailFields( $oTable, $oEntity, $bReadOnly ) {
		if ( $oPlgManage = $this->_oPlgManage ) {
			return $oPlgManage->addDetailFields( $oTable, $oEntity, $bReadOnly, $this );
		}
		return $oTable;
	}
	
	//
	public function insertDetails() {
		if ( $oPlgManage = $this->_oPlgManage ) {
			return $oPlgManage->insertDetails( $this );
		}	
	}
	
	//
	public function updateDetails() {
		if ( $oPlgManage = $this->_oPlgManage ) {
			return $oPlgManage->updateDetails( $this );
		}	
	}
	
	//
	public function deleteDetails() {
		if ( $oPlgManage = $this->_oPlgManage ) {
			return $oPlgManage->deleteDetails( $this );
		}	
	}
	
	
	
}


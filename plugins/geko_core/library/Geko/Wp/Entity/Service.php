<?php

//
class Geko_Wp_Entity_Service extends Geko_Wp_Service
{
	
	const STAT_SUCCESS_ADD = 1;
	const STAT_SUCCESS_EDIT = 2;
	const STAT_SUCCESS_DELETE = 3;
	const STAT_ERROR = 999;
	
	protected $_sInstanceClass;
	protected $_sEntityClass;
	protected $_sManageClass;
	
	protected $_oManage;
	
	protected $_aActions = array();				// array of action objects
	
	
	
	//
	protected function __construct() {
		
		parent::__construct();
		
		//// resolve related classes
		
		$this->_sInstanceClass = get_class( $this );
		
		$this->_sEntityClass = Geko_Class::resolveRelatedClass(
			$this->_sInstanceClass, '_Service', '', $this->_sEntityClass
		);
		
		$this->_sManageClass = Geko_Class::resolveRelatedClass(
			$this->_sEntityClass, '', '_Manage', $this->_sManageClass
		);
		
		if ( $this->_sManageClass ) {
			$this->_oManage = Geko_Singleton_Abstract::getInstance( $this->_sManageClass );
			$oManage = $this->_oManage;
		}

	}
	
	//
	public function addAction( $mAction ) {
		
		if ( is_string( $mAction ) && class_exists( $mAction ) ) {
			$oAction = Geko_Singleton_Abstract::getInstance( $mAction );
		} else {
			$oAction = $mAction;
		}
		
		$this->_aActions[ $oAction->getName() ] = $oAction;
		
		return $this;
	}
	
	//
	public function getModes() {
		return array_keys( $this->_aActions );
	}
	
	//
	public function getManage() {
		return $this->_oManage;
	}
	
	//
	public function getAction( $sAction ) {
				
		if ( !$oAction = $this->_aActions[ $sAction ] ) {
			// default to error action
			$oAction = $this->_aActions[ 'error' ];
		}
		
		return $oAction;
	}
	
	//
	public function getActions( $aActions = NULL ) {
		
		if ( NULL == $aActions ) return $this->_aActions;
		
		$aRet = array();
		foreach ( $aActions as $sAction ) {
			if ( $oAction = $this->_aActions[ $sAction ] ) {
				$aRet[ $sAction ] = $oAction;
			}
		}
		
		return $aRet;
	}
	
	//
	public function getJsonParams( $aActions = NULL ) {
		
		$aActionsJson = array();
		
		$aActions = $this->getActions( $aActions );
		foreach ( $aActions as $sAction => $oAction ) {
			$aActionsJson[ $sAction ] = $oAction->getJsonParams( $this );
		}
		
		return array(
			'name' => $this->_sInstanceClass,
			'actions' => $aActionsJson
		);
	}
	
	// hook methods
	public function initActions() {
		$this
			->addAction( 'Geko_Wp_Entity_Service_Action_Add' )
			->addAction( 'Geko_Wp_Entity_Service_Action_Edit' )
			->addAction( 'Geko_Wp_Entity_Service_Action_Delete' )
			->addAction( 'Geko_Wp_Entity_Service_Action_Error' )
		;
		return $this;
	}
	
	
	
	//
	public function process() {
		
		
		//// set-up vars
		
		$sAction = trim( $_REQUEST[ '_action' ] );		
		
		
		
		// perform initialization
		$this->initActions();
		
		
		
		//// perform actions
		
		$aAjaxResponse = array();
		
		$oAction = $this->getAction( $sAction );
		
		$aAjaxResponse = $oAction->perform( $aAjaxResponse, $this );

		// hook here maybe???
		
		$this->_aAjaxResponse = $aAjaxResponse;
		
		return $this;
		
	}
	
	
	
}



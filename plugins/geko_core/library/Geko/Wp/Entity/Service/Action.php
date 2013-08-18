<?php

//
class Geko_Wp_Entity_Service_Action extends Geko_Singleton_Abstract
{
	
	protected $_sName;
	protected $_sPastTense;
	
	protected $_aStatusValues = array(
		'success' => 1,
		'error' => 999
	);
	
	protected $_sSuccessMsg = '';
	protected $_sErrorMsg = '';
	
	
	
	//
	public function getName() {
		return $this->_sName;
	}
	
	//
	public function getPastTense() {
		return $this->_sPastTense;
	}

	//
	public function getStatusValues() {
		return $this->_aStatusValues;
	}
	
	//
	public function getStatusValue( $sStatus ) {
		if ( $iStatusValue = $this->_aStatusValues[ $sStatus ] ) {
			return $iStatusValue;
		}
		return $this->_aStatusValues[ 'error' ];
	}
	
	//
	public function getJsonParams( $oService ) {
		
		$sAction = $this->getName();
		
		if ( !$this->_sSuccessMsg || !$this->_sErrorMsg ) {
		
			$oManage = $oService->getManage();
			
			$sSubject = $oManage->getSubject();
			$sPastTense = $this->getPastTense();
			
			if ( !$this->_sSuccessMsg ) {
				$this->_sSuccessMsg = sprintf( 'The %s was %s successfully!', $sSubject, $sPastTense );
			}
			
			if ( !$this->_sErrorMsg ) {
				$this->_sErrorMsg = sprintf( 'Failed to %s %s. Please try again.', $sAction, $sSubject );
			}
			
		}
		
		return array(
			'name' => $sAction,
			'success_msg' => $this->_sSuccessMsg,
			'error_msg' => $this->_sErrorMsg,
			'status' => $this->getStatusValues()
		);
		
	}
	
	//
	public function perform( $aAjaxResponse, $oService ) {
		$aAjaxResponse[ 'status' ] = $this->getStatusValue( 'error' );
		return $aAjaxResponse;
	}
	
	
	
}


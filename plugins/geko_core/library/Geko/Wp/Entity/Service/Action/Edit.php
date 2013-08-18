<?php

//
class Geko_Wp_Entity_Service_Action_Edit extends Geko_Wp_Entity_Service_Action
{
	
	protected $_sName = 'edit';
	protected $_sPastTense = 'edited';
	
	//
	public function perform( $aAjaxResponse, $oService ) {
		
		$aAjaxResponse = parent::perform( $aAjaxResponse );
		
		$oManage = $oService->getManage();
		
		// do DB stuff
		if ( FALSE !== $oManage->updateDetails() ) {
			$aAjaxResponse[ 'status' ] = $this->getStatusValue( 'success' );
			$aAjaxResponse[ 'update_values' ] = $oManage->getUpdateValues();
		}
		
		return $aAjaxResponse;
		
	}
	
}


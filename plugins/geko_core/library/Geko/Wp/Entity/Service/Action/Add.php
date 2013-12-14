<?php

//
class Geko_Wp_Entity_Service_Action_Add extends Geko_Wp_Entity_Service_Action
{
	
	protected $_sName = 'add';
	protected $_sPastTense = 'added';
	
	//
	public function perform( $aAjaxResponse, $oService ) {
		
		$aAjaxResponse = parent::perform( $aAjaxResponse );
		
		$oManage = $oService->getManage();
		
		// do DB stuff
		if ( FALSE !== $oManage->insertDetails() ) {
			$aAjaxResponse[ 'status' ] = $this->getStatusValue( 'success' );
			$aAjaxResponse[ 'insert_id' ] = $oManage->getTargetEntityId();
		}
		
		return $aAjaxResponse;
		
	}
	
}


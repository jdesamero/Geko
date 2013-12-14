<?php

//
class Geko_Wp_Entity_Service_Action_Delete extends Geko_Wp_Entity_Service_Action
{
	
	protected $_sName = 'delete';
	protected $_sPastTense = 'deleted';
	
	//
	public function perform( $aAjaxResponse, $oService ) {
		
		$aAjaxResponse = parent::perform( $aAjaxResponse );
		
		$oManage = $oService->getManage();
		
		// do DB stuff
		if ( FALSE !== $oManage->deleteDetails() ) {
			$aAjaxResponse[ 'status' ] = $this->getStatusValue( 'success' );
		}
		
		return $aAjaxResponse;
		
	}

}


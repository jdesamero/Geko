<?php
/*
 * "geko_navigation_management/includes/library/Geko/Wp/NavigationManagement/Service.php"
 * https://github.com/jdesamero/Geko
 *
 * Copyright (c) 2013 Joel Desamero.
 * Licensed under the MIT license.
 */

//
class Geko_Wp_NavigationManagement_Service extends Geko_Wp_Service
{
	
	protected $_aJsonEncodeParams = array(
		'enableJsonExprFinder' => TRUE
	);
	
	
	
	
	
	//
	public function processLoadData() {
		
		$oPageAdmin = Geko_Wp_NavigationManagement_PluginAdmin::getInstance();
		
		$this->setResponseValue( 'data', $oPageAdmin->getNavData() );
		
	}
	
	
	//
	public function processNavGroup() {
		
		$sOp = strtolower( $_REQUEST[ 'ops' ] );		// get the operation
		$iNavGrpIdx = intval( $_REQUEST[ 'nav_group_idx' ] );
		$sLabel = trim( $_POST[ 'nav_group_label' ] );
		$sCode = trim( $_POST[ 'nav_group_code' ] );
		
		$oPageAdmin = Geko_Wp_NavigationManagement_PluginAdmin::getInstance();
		
		$oPageAdmin->saveNavGroup( $sOp, $iNavGrpIdx, $sLabel, $sCode );
		
		$this->setResponseValue( 'redirect', $oPageAdmin->getRedirect( $sOp, $iNavGrpIdx ) );
		
	}
	
	
	//
	public function processSaveData() {
		
		$iNavGrpIdx = intval( $_REQUEST[ 'nav_group_idx' ] );
		$sSerializedData = stripslashes( $_REQUEST[ 'serialized_data' ] );
		
		$oPageAdmin = Geko_Wp_NavigationManagement_PluginAdmin::getInstance();
		
		$oPageAdmin->saveNavData( $iNavGrpIdx, $sSerializedData );
		
	}
	
	
}



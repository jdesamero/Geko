<?php

//
abstract class Geko_Navigation_PageManager_ImplicitLabelAbstract
	extends Geko_Navigation_PageManager_PluginAbstract
{
	
	//
	public function getDefaultParams() {
		
		$aParams = parent::getDefaultParams();
		unset( $aParams['label'] );
		unset( $aParams['title'] );
		
		return $aParams;
	}
	
	
}


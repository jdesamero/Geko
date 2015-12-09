<?php
/*
 * "geko_navigation_management/includes/library/Geko/Navigation/PageManager/ImplicitLabelAbstract.php"
 * https://github.com/jdesamero/Geko
 *
 * Copyright (c) 2013 Joel Desamero.
 * Licensed under the MIT license.
 */

//
abstract class Geko_Navigation_PageManager_ImplicitLabelAbstract
	extends Geko_Navigation_PageManager_PluginAbstract
{
	
	//
	public function getDefaultParams() {
		
		$aParams = parent::getDefaultParams();
		unset( $aParams[ 'label' ] );
		unset( $aParams[ 'title' ] );
		
		return $aParams;
	}
	
	
}



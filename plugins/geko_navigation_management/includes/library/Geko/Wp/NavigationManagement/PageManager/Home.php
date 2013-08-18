<?php

//
class Geko_Wp_NavigationManagement_PageManager_Home
	extends Geko_Navigation_PageManager_ImplicitLabelAbstract
{	
	
	//
	public function outputStyle() {
		?>	
		.type-##type## { background-color: #d3e0ee; border: dotted 1px #2c4f91; }
		<?php
	}
	
	
	//
	public function getManagementData()
	{	
		$aData = parent::getManagementData();
		$aData['homepage_url'] = Geko_Wp::getHomepageUrl( __CLASS__ );
		$aData['homepage_title'] = Geko_Wp::getHomepageTitle( __CLASS__ );
		
		return $aData;
	}
	
	
	
	//
    public static function getDescription() {
    	return 'Wordpress Home Page';
    }

}


<?php

//
class Geko_Wp_NavigationManagement_PageManager_Search
	extends Geko_Navigation_PageManager_ImplicitLabelAbstract
{	
	
	//
	public function outputStyle() {
		?>
		.type-##type## { background-color: #f0e44a; border: dotted 1px #cb753f; }
		<?php
	}
	
	
	//
	public function getManagementData() {
		
		// TO DO: implement properly
		$aData = parent::getManagementData();
		$aData['search_url'] = Geko_Wp::getHomepageUrl( __CLASS__ );
		$aData['search_title'] = 'Search';
		
		return $aData;
	}
	
	
	
	//
    public static function getDescription() {
    	return 'Wordpress Search';
    }

}


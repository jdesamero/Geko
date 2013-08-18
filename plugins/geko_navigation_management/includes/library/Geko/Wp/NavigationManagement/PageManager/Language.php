<?php

//
class Geko_Wp_NavigationManagement_PageManager_Language
	extends Geko_Navigation_PageManager_ImplicitLabelAbstract
{	
	
	//
	public function outputStyle() {
		?>	
		.type-##type## { background-color: violet; border: dotted 1px black; }
		<?php
	}
	
	
	/* //
	public function getManagementData() {
		
		$aData = parent::getManagementData();
		$aData['homepage_url'] = Geko_Wp::getHomepageUrl( __CLASS__ );
		$aData['homepage_title'] = Geko_Wp::getHomepageTitle( __CLASS__ );
		
		return $aData;
	} */
	
	
	
	//
    public static function getDescription() {
    	return 'Wordpress Language Toggle';
    }

}


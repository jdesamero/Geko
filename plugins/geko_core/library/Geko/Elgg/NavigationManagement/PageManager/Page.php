<?php

//
class Geko_Elgg_NavigationManagement_PageManager_Page extends Geko_Navigation_PageManager_Uri
{

	//
	public function outputStyle() {
		?>	
		.type-##type## { background-color: blanchedalmond; border: dotted 1px chocolate; }
		<?php
	}
	
	//
    public static function getDescription() {
    	return 'Elgg URL';
    }

}



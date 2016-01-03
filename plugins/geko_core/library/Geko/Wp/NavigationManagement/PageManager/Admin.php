<?php
/*
 * "geko_core/library/Geko/Wp/NavigationManagement/PageManager/Admin.php"
 * https://github.com/jdesamero/Geko
 *
 * Copyright (c) 2013 Joel Desamero.
 * Licensed under the MIT license.
 */

//
class Geko_Wp_NavigationManagement_PageManager_Admin extends Geko_Navigation_PageManager_Uri
{

	//
	public function outputStyle() {
		?>	
		.type-##type## { background-color: #e4e7dd; border: dotted 1px darkolivegreen; }
		<?php
	}
	
	//
    public static function getDescription() {
    	return 'Wordpress Admin URL';
    }

}



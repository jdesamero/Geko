<?php
/*
 * "geko_core/library/Geko/Wp/NavigationManagement/Integration/PageManager/Admin.php"
 * https://github.com/jdesamero/Geko
 *
 * Copyright (c) 2013 Joel Desamero.
 * Licensed under the MIT license.
 */

//
class Geko_Wp_NavigationManagement_Integration_PageManager_Admin
	extends Geko_Wp_NavigationManagement_PageManager_Admin
{

	//
	public function outputStyle() {
		?>	
		.type-##type## { background-color: lavender; border: solid 1px indigo; }
		<?php
	}
	
}



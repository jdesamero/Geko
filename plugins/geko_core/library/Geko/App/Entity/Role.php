<?php
/*
 * "geko_core/library/Geko/App/Entity/Role.php"
 * https://github.com/jdesamero/Geko
 *
 * Copyright (c) 2013 Joel Desamero.
 * Licensed under the MIT license.
 */

//
class Geko_App_Entity_Role extends Geko_App_Entity
{
	
	//
	public function init() {
		
		parent::init();
		
		$this->addPlugin( 'Geko_App_Meta_Plugin_Entity' );
		
		return $this;
	}
	
	
}



<?php
/*
 * "geko_core/library/Geko/App/Entity/Role/Record.php"
 * https://github.com/jdesamero/Geko
 *
 * Copyright (c) 2013 Joel Desamero.
 * Licensed under the MIT license.
 */

//
class Geko_App_Entity_Role_Record extends Geko_Entity_Record
{
	
	//
	public function init() {
	
		$this->addPlugin( 'Geko_App_Meta_Plugin_Record' );
		
		parent::init();
		
		return $this;
	}
	
	
}


